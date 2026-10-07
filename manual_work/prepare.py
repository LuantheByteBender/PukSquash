from pathlib import Path
import subprocess, json
from docx import Document
from lxml import etree
base=Path.cwd(); out=base/'manual_work'; snap=out/'firebase_source'; snap.mkdir(exist_ok=True)
ref='fe3e361'
paths=subprocess.check_output(['git','ls-tree','-r','--name-only',ref],text=True).splitlines()
selected=[p for p in paths if p.startswith(('resources/js/','app/','dataconnect/','routes/','tests/Feature/','config/')) and p.endswith(('.php','.js','.vue','.gql','.yaml'))]
selected += ['package.json','composer.json','firebase.json','bootstrap/app.php']
for p in selected:
    target=snap/p; target.parent.mkdir(parents=True,exist_ok=True)
    if target.exists(): continue
    target.write_bytes(subprocess.check_output(['git','show',f'{ref}:{p}']))
print('Snapshot',ref,len(selected),'files')
for name in ['business','feasibility','functional']:
    text=(out/(name+'.txt')).read_text(encoding='utf-8')
    lines=text.splitlines()
    print('\nCONTEXT',name)
    print('\n'.join(x for x in lines if any(w in x.lower() for w in ['purpose','problem','phase','booking limit','five','consecutive','24 hour','registration','authentication','cancel']) and len(x)<1800)[:11000])
doc=Document('C:/Users/300w12/Downloads/Campus Marketplace - Developer Manual (4).docx')
for i in [26,27,29,114,115,116]:
    p=doc.paragraphs[i]
    print('PARA XML',i,p.text[:100],p._p.xml[:4500])
vbs='''Option Explicit
Dim app, doc
Set app = CreateObject("Word.Application")
app.Visible = False
app.DisplayAlerts = 0
Set doc = app.Documents.Open("C:\\Users\\300w12\\Downloads\\Campus Marketplace - Developer Manual (4).docx", False, True, False)
doc.ExportAsFixedFormat "OUTPUT", 17
WScript.Echo "Pages " & doc.ComputeStatistics(2)
doc.Close False
app.Quit
'''.replace('OUTPUT',str(out/'reference_render'/'reference.pdf'))
(out/'reference_render').mkdir(exist_ok=True)
(out/'render_reference.vbs').write_text(vbs,encoding='utf-8')
