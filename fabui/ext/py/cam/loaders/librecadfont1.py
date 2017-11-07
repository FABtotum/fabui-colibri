import os

class Symbol:
    def __init__(self, char, code):
        self.char = char
        self.code = code
        self.ref = None
        self.lines = []

class LibreCADFont1(object):
    
    def __init__(self):
        self.meta = {}
        self.symbols = {}

    def getSymbol(self, char):
        return self.symbols[char]

    def load_from_file(self, filename):
        """
        Load font data from a file.
        """
        with open(filename) as f:
            lines = f.readlines()
            #print lines
            symbol = None
            lastmeta = None
            valid_meta = ['Format','Creator','Name','Version','Encoding',
                'LetterSpacing','WordSpacing','LineSpacingFactor','Created',
                'Last modified','Author','License','Notes']
            
            for line in lines:
                
                if line == '\n':
                    if symbol:
                        self.symbols[symbol.char] = symbol
                    symbol = None
                    continue
                
                if line[0] == '#':
                    data = line[1:].strip().split(':',1)
                        
                    if (data[0] not in valid_meta) or len(data) < 2:
                        if data[0] and lastmeta:
                            self.meta[ lastmeta ] += ' ' + line[1:].strip()
                        continue
                            
                    self.meta[ data[0] ] = data[1].strip()
                    lastmeta = data[0]
                elif line[0] == '[':
                    if line[1] == '#':
                        code = str(line[2:6])
                        code_num = int(code, 16)
                        sym = unichr(code_num)
                        symbol = Symbol(sym, code)
                    else:
                        code = str(line[1:5])
                        sym = line[7]
                        symbol = Symbol(sym, code)
                else:
                    if symbol:
                        points = []
                        segments = line.strip().split(';')
                        skip = False
                        for seg in segments:
                            if seg[0] == 'C':
                                ref = seg[1:5]
                                code_num = int(ref, 16)
                                sym = unichr(code_num)
                                symbol.ref = sym
                                skip = True
                                break
                                
                            else:
                                pts = seg.split(',')
                                if len(pts) == 3:
                                    points.append( (float(pts[0]), float(pts[1]), float(pts[2][1:])) )
                                else:
                                    points.append( (float(pts[0]), float(pts[1])) )
                        if not skip:
                            symbol.lines.append( points )
                            
        print self.meta

def readfile(filename):
    """
    Create a LibreCADFont1 object and load it's data from a file.
    """
    lff = LibreCADFont1()
    if os.path.exists(filename):
        lff.load_from_file(filename)
        return lff
    else:
        return None
