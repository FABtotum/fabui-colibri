import argparse
import gettext
from fabtotum.fabui.config  import ConfigService
from threading import Event, Thread
import pycurl, os, json

# Set up message catalog access
tr = gettext.translation('print', 'locale', fallback=True)
_ = tr.ugettext


class UpdateApplication():
    
    def __init__(self, task_id, endpoint, bundles, monitor_file):
        
        self.task_id      = task_id
        self.endpoint     = endpoint
        self.bundles      = bundles
        self.monitor_file = monitor_file
        self.monitor_data = {}
        
        self.initMonitorData()
        
    def initMonitorData(self):
        self.monitor_data = {
            "task" : {
                "id" : self.task_id
            },
            "gpusher": {},
            "override": {},
            "update": {
                "bundles": {},
                "current": '',
                "number": len(self.bundles)
            }
        }
        for bundle in self.bundles:
            self.monitor_data['update']['bundles'][bundle] = {
                "status" : '',
                'download_progress': 0,
                'file_size': 0,
                'speed': 0
            }
        
        
    def writeMonitorFile(self):
        print self.monitor_data
        monitor_file = open(self.monitor_file,'w+')
        monitor_file.write(json.dumps(self.monitor_data))
        monitor_file.close()
    
    def updateMonitorFile(self):
        print "update monitor file"
        
    def download(self, bundle_name):
        #print "download: ", bundle_name
        self.monitor_data['update']['bundles'][bundle_name]['status'] = 'downloading'
        curl = pycurl.Curl()
        curl.setopt(pycurl.URL, self.endpoint + 'armhf/bundles/' + bundle_name + '/latest' )
        curl.setopt(pycurl.FOLLOWLOCATION, 1)
        curl.setopt(pycurl.MAXREDIRS, 5)
        filename = bundle_name
        if os.path.exists(filename):
            f = open(filename, "ab")
            curl.setopt(pycurl.RESUME_FROM, os.path.getsize(filename))
        else:
            f = open(filename, "wb")
        
        curl.setopt(pycurl.WRITEDATA, f)
        curl.setopt(pycurl.NOPROGRESS, 0)
        curl.setopt(pycurl.PROGRESSFUNCTION, self.progress)
        try:
            curl.perform()
            self.monitor_data['update']['bundles'][bundle_name]['status'] = 'downloaded'
        except:
            print "ERROR"
            return False
            pass
        return True
        
    def progress(self, download_t, download_d, upload_t, upload_d):
        
        try:
            #print "Total to download", download_t
            #print "Total downloaded", download_d
            self.monitor_data['update']['bundles'][self.monitor_data['update']['current']]['file_size'] = download_t
            #self.monitor_data['update']['bundles'][self.monitor_data['update']['current']]['speed'] = download_d
            self.monitor_data['update']['bundles'][self.monitor_data['update']['current']]['download_progress'] = ( download_d / download_t ) * 100
            #print "Total to upload", upload_t
            #print "Total uploaded", upload_d
            self.writeMonitorFile()
        except:
            pass
        
        
    def update(self, bundle):
        print "update"
        self.monitor_data['update']['current'] = bundle
        self.writeMonitorFile()
        self.download(bundle)
        
    def install(self):
        print "install"
        
    
    def run(self):
        #print self.monitor_data
        print self.endpoint
        #print len(self.bundles)
        #print self.monitor_data['update']
        self.writeMonitorFile()
        for bundle in self.bundles:
            self.update(bundle)
        self.writeMonitorFile()
        
        


def main():
    config = ConfigService()
    parser = argparse.ArgumentParser(formatter_class=argparse.ArgumentDefaultsHelpFormatter)
    parser.add_argument("-T", "--task-id",     help=_("Task ID."),      default=0)
    parser.add_argument('-b','--bundles', nargs='+', help='<Required> Set flag', required=True)
    
    args = parser.parse_args()
    
    bundles      = args.bundles
    task_id      = args.task_id
    endpoint     = config.get('updates', 'colibri_endpoint')
    monitor_file = config.get('general', 'task_monitor')
    
    app = UpdateApplication(task_id, endpoint, bundles, monitor_file)
    app_thread = Thread(target = app.run)
    app_thread.start()
    app_thread.join()

if __name__ == "__main__":
    main()