from distutils.core import setup, Extension
import os, sys
import subprocess

def pkgconfig(*packages, **kw):
    """
    Query pkg-config for library compile and linking options. Return configuration in distutils
    Extension format.

    Usage: 

    pkgconfig('opencv')

    pkgconfig('opencv', 'libavformat')

    pkgconfig('opencv', optional='--static')

    pkgconfig('opencv', config=c)

    returns e.g.  

    {'extra_compile_args': [],
     'extra_link_args': [],
     'include_dirs': ['/usr/include/ffmpeg'],
     'libraries': ['avformat'],
     'library_dirs': []}

     Intended use:

     distutils.core.Extension('pyextension', sources=['source.cpp'], **c)

     Set PKG_CONFIG_PATH environment variable for nonstandard library locations.

    based on work of Micah Dowty (http://code.activestate.com/recipes/502261-python-distutils-pkg-config/)
    """
    config = kw.setdefault('config', {})
    optional_args = kw.setdefault('optional', '')

    # { <distutils Extension arg>: [<pkg config option>, <prefix length to strip>], ...}
    flag_map = {'include_dirs': ['--cflags-only-I', 2],
                'library_dirs': ['--libs-only-L', 2],
                'libraries': ['--libs-only-l', 2],
                'extra_compile_args': ['--cflags-only-other', 0],
                'extra_link_args': ['--libs-only-other', 0],
                }
    for package in packages:
        for distutils_key, (pkg_option, n) in flag_map.items():
            items = subprocess.check_output(['pkg-config', optional_args, pkg_option, package]).decode('utf8').split()
            config.setdefault(distutils_key, []).extend([i[n:] for i in items])
    return config
    
################################################################################

# Ensure C++ compiler is used
os.environ["CC"] = os.environ["CXX"]

pkgcfg_args = pkgconfig('opencv')

# Add NumPY include path
sysroot = os.environ.get('_python_sysroot')
vi = sys.version_info
python_subdir = "python{0}.{1}".format(vi.major, vi.minor)
numpy_inc = os.path.join(sysroot, 'usr/lib', python_subdir, 'site-packages/numpy/core/include')

pkgcfg_args['include_dirs'].append(numpy_inc)
pkgcfg_args['libraries'].append('boost_python')

hello_ext = Extension('fabtotum.speedups.triangulation',
                    sources=["triangulation.cpp", 
							 "pyboost_cv2_converter.cpp", 
							 "pyboost_cv3_converter.cpp"],
                    **pkgcfg_args
                 )

setup (
    name            = 'speedups',
    version         = '0.1',
    author          = 'Daniel Kesler',
    author_email    = 'kesler.daniel@gmail.com',
    license         = 'GPLv3',
    description     = 'Triangulation accelerator',
    ext_modules     = [hello_ext]
)
