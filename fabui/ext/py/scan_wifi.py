import os
import json
def shell_exec(cmd):
    stdin,stdout = os.popen2(cmd)
    stdin.close()
    lines = stdout.readlines(); 
    stdout.close()
    return lines

raw_results = shell_exec('iwlist {0} scan'.format('wlan0'))

results = {}

cell_id = ""
important = ['ESSID', 'Channel', 'Frequency', 'Encryption key']
cell = {}
for line in raw_results:
	line = line.strip()
	tags = line.split(':')
	if line.startswith('Cell'):
		cell_id = line.split()[1]
		cell = results[cell_id] = {}
		
	if tags[0] in important:
		cell[tags[0]] = tags[1]
		
	if tags[0].startswith('Quality'):
		tmp = line.split()[0].split('=')[1].split('/')
		q = float(tmp[0])*100.0 / float(tmp[1])
		cell['Quality'] = q
		
print json.dumps(results)

