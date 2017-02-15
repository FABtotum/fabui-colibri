import os
import jinja2

templateSearchPath = os.path.dirname(os.path.realpath(__file__)) + '/' + "templates"
templateLoader = jinja2.FileSystemLoader( searchpath=templateSearchPath )
templateEnv = jinja2.Environment( loader=templateLoader )

def create_from_template(template, output, env = {}, overwrite = False):
	if not os.path.exists(output) or overwrite:
		t = templateEnv.get_template(template)
		if 'filename' not in env:
			env['filename'] = os.path.basename(output)
		outputText = t.render( env )
		if output:
			f = open(output, 'w')
			f.write(outputText)
			f.close()
			print("create '{:s}' based on '{:s}'".format(output,template))
		else:
			print("create based on '{:s}'".format(template))
			print(outputText)

def create_dir(f):
	if not os.path.exists(f):
		os.makedirs(f)
		print( 'mkdir {:s}'.format(f) )

def create_link(src, dst, overwrite = False):
	if os.path.lexists(dst):
		if overwrite:
			os.remove(dst)
			os.symlink(src, dst)
			print( 'softlink {:s} -> {:s}'.format(dst,src) )
	else:
		os.symlink(src, dst)
		print( 'softlink {:s} -> {:s}'.format(dst,src) )

def build_path(*args):
	return os.path.join(*args)
