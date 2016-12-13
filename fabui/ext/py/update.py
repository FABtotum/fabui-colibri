import argparse, gettext, os,time, json,pycurl
import commands
import threading
from fabtotum.fabui.config  import ConfigService

from fabtotum.update.factory import Factory
from fabtotum.update.bundle  import Bundle
from fabtotum.update.version import RemoteVersion

tr = gettext.translation('update', 'locale', fallback=True)
_ = tr.ugettext

factory = Factory()

''' ==================================================================================================== '''
''' '''
class Update(threading.Thread):
    def __init__(self, bundles_list, endpoint, temp_folder):
        super(Update, self).__init__()
        self.bundles_list = bundles_list
        self.endpoint     = endpoint
        self.temp_folder  = temp_folder
        self.remote_data  = RemoteVersion(endpoint)
        self.loaded_bundles = {}
        
    def run(self):
        
        remote_bundles = self.remote_data.getData('bundles')
        ''' === loading bundles '''
        for bundle_name in self.bundles_list:
            bundle = Bundle(bundle_name, remote_bundles[bundle_name])
            factory.addBundle(bundle)
            self.addBundle(bundle)
        
        factory.setStatus('running')
        
        for bundle_name in self.loaded_bundles:
            self.do_download(self.loaded_bundles[bundle_name])
        
        for bundle_name in self.loaded_bundles:
            self.do_install(self.loaded_bundles[bundle_name])
        
        factory.setStatus('completed')
        factory.do_stop()
            
    def addBundle(self, bundle):
        self.loaded_bundles[bundle.getName()] = bundle
        
    def do_download(self, bundle):
        print "do download: ", bundle.getName()
        factory.setCurrentBundle(bundle.getName())
        factory.setCurrentStatus('downloading')
        self.download(bundle, 'bundle')
        self.download(bundle, 'md5')
        factory.setCurrentStatus('downloaded')
        bundle.setStatus('downloaded')
        factory.updateBundle(bundle)
        
    def do_install(self, bundle):
        print "installing: " , bundle.getName()
        bundle.setStatus('installing')
        factory.updateBundle(bundle)
        #print commands.getstatusoutput('colibrimngr install -postpone ' + self.temp_folder +  'fabui/' + bundle.getBundleFile().getName())
    
    def download(self, bundle, type):
        
        bundle.setStatus('downloading')
        factory.updateBundle(bundle)
        factory.setCurrentFileType(type)
        
        file_endpoint = bundle.getFile(type).getEndpoint()
        file_name = bundle.getFile(type).getName()
        
        curl = pycurl.Curl()
        curl.setopt(pycurl.URL, self.endpoint + 'armhf/' + file_endpoint)
        curl.setopt(pycurl.FOLLOWLOCATION, 1)
        curl.setopt(pycurl.MAXREDIRS, 5)
        
        file_to_write = open(self.temp_folder + 'fabui/' + file_name, "wb")
        
        curl.setopt(pycurl.WRITEDATA, file_to_write)
        curl.setopt(pycurl.NOPROGRESS, 0)
        curl.setopt(pycurl.PROGRESSFUNCTION, self.download_progress)
        
        curl.perform()
        
        bundle.getFile(type).setStatus('downloaded')
        factory.updateBundle(bundle)
        
        
    def download_progress(self, file_size, downloaded, upload_t, upload_d):
        
        try:
            current_file_type = factory.getCurrentFileType()
            bundle = factory.getBundle(factory.getCurrentBundle())
            file = bundle.getFile(current_file_type)
            
            file.setSize(file_size)
            file.setProgress(( downloaded / file_size ) * 100)
            file.setStatus('downloading')
            
            bundle.updateFile(file, current_file_type)
            factory.updateBundle(bundle)
        
        except:
            pass
        
''' ==================================================================================================== '''
''' '''
class MonitorWriter(threading.Thread):
    def __init__(self, monitor_file):
        super(MonitorWriter, self).__init__()
        self.file = monitor_file
        self.stop = False
        self.every = 1
        self.write()
        
    def write(self):
        monitor_file = open(self.file,'w+')
        monitor_file.write(json.dumps(factory.serialize()))
        monitor_file.close()
        #print json.dumps(factory.serialize())
        #print "write"
        
    def run(self):
        while factory.getStop() == False :
            self.write()
            time.sleep(self.every)
        self.write()
''' ==================================================================================================== '''

def main():
    config = ConfigService()
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("-T", "--task-id",     help=_("Task ID."),      default=0)
    parser.add_argument('-b','--bundles', help='<Required> Set flag', required=True)
    
    args = parser.parse_args()
        
    bundles      = args.bundles.split(',')
    task_id      = args.task_id
    
    monitor_file = config.get('general', 'task_monitor')
    endpoint     = config.get('updates', 'colibri_endpoint')
    temp_folder  = config.get('general', 'bigtemp_path')
    
    factory.setTaskId(task_id)
    factory.setPid(os.getpid())
    factory.setStatus('preparing')
    
    threads = [
        MonitorWriter(monitor_file),
        Update(bundles, endpoint, temp_folder)
    ]
    
    for thr in threads:
         thr.start()
    
    while True:
        alives = []
        for thr in threads:
            alives.append(thr.isAlive())
            thr.join(0.05)
            time.sleep(0.2)
        if not all(alives):
            break
    
    
    
if __name__ == "__main__":
    main()