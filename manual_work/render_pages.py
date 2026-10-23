import pypdfium2 as pdfium
from pathlib import Path
from PIL import Image, ImageOps, ImageDraw
import sys
p=Path(sys.argv[1]); out=p.parent/(p.stem+'_pages');out.mkdir(exist_ok=True)
doc=pdfium.PdfDocument(str(p))
for n in range(len(doc)):
    page=doc[n]; im=page.render(scale=1).to_pil().convert('RGB');im.save(out/f'page-{n+1}.png');page.close()
for start in range(0,len(doc),4):
    sheet=Image.new('RGB',(1220,1744),'#dddddd')
    for n in range(start,min(start+4,len(doc))):
        im=Image.open(out/f'page-{n+1}.png'); x=((n-start)%2)*610; y=((n-start)//2)*872
        sheet.paste(im,(x,y+20));ImageDraw.Draw(sheet).text((x+10,y+3),f'Page {n+1}',fill='black')
    sheet.save(out/f'sheet-{start//4+1}.png')
print(str(out),len(doc),'pages')
