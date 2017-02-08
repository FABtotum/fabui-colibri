import os
import shlex, subprocess
import shutil

def makedirs(path):
    """ python implementation of `mkdir -p` """
    try:
        os.makedirs(path)
    except OSError as exc:  # Python >2.5
        if exc.errno == errno.EEXIST and os.path.isdir(path):
            pass
        else:
            raise

def create_dir(f):
    if not os.path.exists(f):
        try:
            os.makedirs(f)
        except OSError as exc:  # Python >2.5
            if exc.errno == errno.EEXIST and os.path.isdir(path):
                pass
            else:
                raise
        print( 'mkdir {:s}'.format(f) )

def create_link(src, dst, overwrite = True):
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

def remove_dir(dirname):
    shutil.rmtree(dirname)

def copy_files(src, dst):
    cmd = 'cp -aR {0} {1}'.format(src, dst)
    try:
        output = subprocess.check_output( cmd, shell=True )
    except subprocess.CalledProcessError as e:
        return False
        
    return True

def find_file(filename, in_path):
    output = ""
    
    cmd = "find {0} -name {1}".format(in_path, filename)
    
    try:
        output = subprocess.check_output( shlex.split(cmd) )
    except subprocess.CalledProcessError as e:
        pass
        
    print ">>", cmd, output
        
    return output.split('\n')
