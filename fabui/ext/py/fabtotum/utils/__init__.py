import re
import os
import errno
import shlex, subprocess
import shutil
import pwd

def makedirs(path):
    """ python implementation of `mkdir -p` """
    try:
        os.makedirs(path)
    except OSError as exc:  # Python >2.5
        if exc.errno == errno.EEXIST and os.path.isdir(path):
            pass
        else:
            raise

def create_dir(f, owner="www-data"):
    if not os.path.exists(f):
        try:
            os.makedirs(f)
            uid, gid =  pwd.getpwnam(owner).pw_uid, pwd.getpwnam(owner).pw_uid
            os.chown(f, uid, gid)
        except OSError as exc:  # Python >2.5
            if exc.errno == errno.EEXIST and os.path.isdir(f):
                pass
            else:
                raise

def create_link(src, dst, overwrite = True):
    if os.path.lexists(dst):
        if overwrite:
            os.remove(dst)
            os.symlink(src, dst)
    else:
        os.symlink(src, dst)

def build_path(*args):
    return os.path.join(*args)

def remove_dir(dirname):
    shutil.rmtree(dirname)
    return True

def remove_file(fn):
    try:
        os.unlink(fn)
    except:
        return False
    return True
    
def copy_files(src, dst, owner="www-data"):
    cmd = 'cp -aR {0} {1}'.format(src, dst)
    uid, gid =  pwd.getpwnam(owner).pw_uid, pwd.getpwnam(owner).pw_uid
    try:
        output = subprocess.check_output( cmd, shell=True )
        output2 = subprocess.check_output('chown -R {0}:{1} {2}'.format(uid, gid, dst), shell=True)
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
        
    return output.split('\n')

def glob2re(pat):
    """Translate a shell PATTERN to a regular expression.

    There is no way to quote meta-characters.
    """

    i, n = 0, len(pat)
    res = ''
    while i < n:
        c = pat[i]
        i = i+1
        if c == '*':
            #res = res + '.*'
            res = res + '[^/]*'
        elif c == '?':
            #res = res + '.'
            res = res + '[^/]'
        elif c == '[':
            j = i
            if j < n and pat[j] == '!':
                j = j+1
            if j < n and pat[j] == ']':
                j = j+1
            while j < n and pat[j] != ']':
                j = j+1
            if j >= n:
                res = res + '\\['
            else:
                stuff = pat[i:j].replace('\\','\\\\')
                i = j+1
                if stuff[0] == '!':
                    stuff = '^' + stuff[1:]
                elif stuff[0] == '^':
                    stuff = '\\' + stuff
                res = '%s[%s]' % (res, stuff)
        else:
            res = res + re.escape(c)
    return res + '\Z(?ms)'
