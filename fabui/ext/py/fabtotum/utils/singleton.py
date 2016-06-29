class Singleton(type):
    """
    Class to ensure singleton behaviour.
    
    .. code-block:: python

        from fabtotum.utils.singleton import Singleton
        class MyClass:
            __metaclass__ = Singleton
    """
    _instances = {}
    def __call__(cls, *args, **kwargs):
        if cls not in cls._instances:
            cls._instances[cls] = super(Singleton, cls).__call__(*args, **kwargs)
        return cls._instances[cls]
