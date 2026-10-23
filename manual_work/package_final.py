from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
import json

root = Path(__file__).resolve().parents[1]
folder = root / 'output' / 'PukSquash_Developer_Manual'
files = sorted(folder.glob('*.docx')) + [folder / 'READ_ME.txt']
assert len(files) == 14 and all(p.is_file() for p in files)
target = root / 'output' / 'PukSquash_Developer_Manual_Firebase.zip'
with ZipFile(target, 'w', ZIP_DEFLATED) as archive:
    for path in files:
        archive.write(path, path.name)
with ZipFile(target) as archive:
    assert archive.testzip() is None
    assert len(archive.namelist()) == 14
print(json.dumps({'archive': str(target), 'documents': 13, 'pages': 52, 'bytes': target.stat().st_size}))
