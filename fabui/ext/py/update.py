import argparse, gettext, os,time, json,pycurl, re,sys
import commands
import threading

from fabtotum.fabui.config  import ConfigService
from fabtotum.database      import Database, timestamp2datetime, TableItem
from fabtotum.database.task import Task

from fabtotum.fabui.gpusher import GCodePusher

from fabtotum.utils.blink    import Blink

from fabtotum.update.factory import UpdateFactory
from fabtotum.update.bundle  import Bundle
from fabtotum.update.version import RemoteVersion

tr = gettext.translation('update', 'locale', fallback=True)
_ = tr.ugettext

config = ConfigService()
factory = UpdateFactory(config)

''' ==================================================================================================== '''
''' '''
class Update(GCodePusher):
    def __init__(self, task_id, bundles_list, endpoint, temp_folder,  config):
        super(Update, self).__init__(config.get('general', 'trace'), config.get('general', 'task_monitor'), use_stdout=False)
        
        self.task_id      = task_id
        self.bundles_list = bundles_list
        self.endpoint     = endpoint
        self.temp_folder  = temp_folder
        self.config       = config
        
        self.remote_data  = RemoteVersion(endpoint)
        self.loaded_bundles = {}
        
        self.blink = Blink(self.config.get('general', 'trace'), self.config.get('general', 'task_monitor'))
        
    def playBeep(self):
        self.send('M300')
        
    def run(self):
        
        self.playBeep()
        
        blink_thread = threading.Thread( 
            target = self.blink.run, 
            args=( ['blue'] ) 
        )
        
        blink_thread.start()
        
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
        
        factory.setCurrentStatus('completed')
        factory.setStatus('completed')
        factory.do_stop()
        factory.update_task_db()
        self.blink.stopBlinking()
        self.playBeep()
            
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
        factory.setCurrentStatus('installing')
        factory.setCurrentBundle(bundle.getName())
        factory.updateBundle(bundle)
        #print 'colibrimngr install -postpone ' + self.temp_folder +  'fabui/' + bundle.getBundleFile().getName()
        install_output =  commands.getstatusoutput('colibrimngr install -postpone ' + self.temp_folder +  'fabui/' + bundle.getBundleFile().getName())
        
        matches = re.search(r"Bundle\sis\sinstalled", install_output[1], re.IGNORECASE)
        
        if(matches):
            print "Bundle installed"
            bundle.setStatus('installed')
            factory.incraeseUpdatedCount()
        else:
            print "Bundle not installed"
            bundle.setStatus('error')
            bundle.setMessage(install_output[1])
            
        factory.updateBundle(bundle)
        print "installed"
        
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
class MonitorWriter():
    def __init__(self, monitor_file):
        #super(MonitorWriter, self).__init__()
        self.file = monitor_file
        self.stop = False
        self.every = 1
        self.write()
        
    def write(self):
        monitor_file = open(self.file,'w+')
        monitor_file.write(json.dumps(factory.serialize()))
        monitor_file.close()
        print json.dumps(factory.serialize())
        
        
    def run(self):
        while factory.getStop() == False :
            self.write()
            time.sleep(self.every)
        self.write()
''' ==================================================================================================== '''

def main():
    
    
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
    
    try:
        appMonitor = MonitorWriter(monitor_file)
        appUpdate  = Update(task_id, bundles, endpoint, temp_folder, config)
        
        #threads = [
        monitorAppThread = threading.Thread(target = appMonitor.run)
        updateAppThread  = threading.Thread(target = appUpdate.run)
        #]
        
        
        monitorAppThread.start()
        updateAppThread.start()
    
        #monitorAppThread.loop()          # app.loop() must be started to allow callbacks
        #updateAppThread.loop()
        
        monitorAppThread.join()
        updateAppThread.join()
        
    except pycurl.error, e:
        factory.setStatus('aborted')
        factory.setError(True)
        factory.setMessage(e[1])
    except:
        factory.setStatus('aborted')
        factory.setError(True)
        factory.setMessage(sys.exc_info()[0])
        
    finally:
        appMonitor.write()
        factory.update_task_db()
        
    
if __name__ == "__main__":
    main()