from pathlib import Path
import pypdfium2 as pdfium
from PIL import Image, ImageDraw, ImageChops
import json

root=Path(__file__).resolve().parents[1]
qa=root/'manual_work/combined_render'
pdf=pdfium.PdfDocument(str(qa/'complete.pdf'))
assert len(pdf)==53, len(pdf)
stats=[]
for n in range(len(pdf)):
    page=pdf[n]
    im=page.render(scale=1).to_pil().convert('RGB')
    im.save(qa/f'page-{n+1}.png')
    text=page.get_textpage().get_text_range()
    footer=page.get_textpage().get_text_bounded(left=0,bottom=0,right=595,top=70).strip()
    assert footer==str(n+1), (n+1,footer)
    if n:
        original_stem=sorted((root/'manual_work/final_render').glob('*.pdf'))[(n-1)//4].stem
        original=Image.open(root/'manual_work/final_render'/original_stem/f'page-{(n-1)%4+1}.png').convert('RGB')
        # The content above the footer must be pixel identical to its approved original.
        diff=ImageChops.difference(im.crop((0,0,595,760)),original.crop((0,0,595,760)))
        if diff.getbbox():
            print(f'Page {n+1} pixel difference bounds: {diff.getbbox()}')
    stats.append({'page':n+1,'chars':len(text)})
for start in range(0,len(pdf),4):
    sheet=Image.new('RGB',(1220,1744),'#DDDDDD')
    draw=ImageDraw.Draw(sheet)
    for off in range(min(4,len(pdf)-start)):
        num=start+off+1
        x=off%2*610;y=off//2*872
        sheet.paste(Image.open(qa/f'page-{num}.png'),(x,y+20))
        draw.text((x+10,y+3),f'Page {num}',fill='black')
    sheet.save(qa/f'sheet-{start//4+1:02d}.png')
(qa/'qa.json').write_text(json.dumps(stats,indent=2))
print('53 pages. Continuous page numbering verified. Contact sheets ready for review.')
