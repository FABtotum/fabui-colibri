from distutils.core import setup, Extension
import os
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

os.environ["CC"] = "g++"

pkgcfg = pkgconfig('opencv')

pkgcfg['libraries'].append('boost_python')
hello_ext = Extension('_triangulation',
                    #~ sources=["triangulation_boost.cpp", "conversion.cpp"],
                    sources=["triangulation.cpp", "triangulation.i"],
                    swig_opts=['-c++'],
                    **pkgcfg
                 )

setup (
    name            = 'triangulation',
    version         = '0.1',
    author          = 'Daniel Kesler',
    author_email    = 'kesler.daniel@gmail.com',
    license         = 'GPLv3',
    description     = 'Triangulation accelerator',
    ext_modules     = [hello_ext]
)
