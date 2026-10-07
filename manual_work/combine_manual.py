from pathlib import Path
from copy import deepcopy
from zipfile import ZipFile, ZIP_DEFLATED
from lxml import etree
from docx import Document
from docx.shared import Pt
from docx.oxml import OxmlElement
from docx.oxml.ns import qn

root = Path(__file__).resolve().parents[1]
sources = sorted((root/'output/PukSquash_Developer_Manual').glob('*.docx'))
assert len(sources) == 13
target = root/'output/PukSquash_Developer_Manual_Complete_Firebase.docx'
doc = Document(sources[0])
body = doc.element.body
section = deepcopy(body.sectPr)
for el in list(body):
    body.remove(el)
body.append(section)
doc.add_paragraph('PukSquash Developer Manual', 'Title')
p = doc.add_paragraph('Complete Firebase edition')
p.runs[0].bold = True
p.runs[0].font.size = Pt(16)
doc.add_paragraph('4 October 2026')
doc.add_paragraph('This manual brings together project setup and all twelve functionality sections. Each section includes the relevant source files, API behaviour, code explanations and verification guidance.')
doc.add_paragraph('The implementation sections document Firebase Authentication and Firebase Data Connect at repository revision fe3e361. Registration payments are presented as a proposed Firebase extension.')
p = doc.add_paragraph('Contents')
p.runs[0].bold = True
p.runs[0].font.size = Pt(14)
names = ['Project Setup and Firebase Data Layer', 'Registration Login and Access Control', 'User Profile Management', 'Court Availability Grid', 'Court Booking Creation', 'Joining an Open Booking', 'My Bookings History and Cancellation', 'Coach Availability and Notice Board', 'Private Coaching Schedule', 'Team Practice Bookings', 'Administrator User Management', 'Administrator Court Management', 'Registration Payments and Firebase Extension']
for i, name in enumerate(names):
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(6)
    link = OxmlElement('w:hyperlink')
    link.set(qn('w:anchor'), f'part_{i:02d}')
    run = OxmlElement('w:r')
    text = OxmlElement('w:t')
    text.text = f'{i:02d}  {name}  —  {2+i*4}'
    run.append(text)
    link.append(run)
    p._p.append(link)
for i, source in enumerate(sources):
    doc.add_page_break()
    incoming = Document(source)
    with ZipFile(sources[0]) as base, ZipFile(source) as other:
        for part in ['word/styles.xml', 'word/numbering.xml']:
            assert base.read(part) == other.read(part), part
    elements = [deepcopy(el) for el in incoming.element.body if el.tag != qn('w:sectPr')]
    for el in elements:
        assert not el.xpath('.//w:drawing | .//w:object | .//w:sectPr')
        assert not any(k.startswith('{http://schemas.openxmlformats.org/officeDocument/2006/relationships}') for n in el.iter() for k in n.attrib)
    start = OxmlElement('w:bookmarkStart')
    start.set(qn('w:id'), str(1000+i))
    start.set(qn('w:name'), f'part_{i:02d}')
    end = OxmlElement('w:bookmarkEnd')
    end.set(qn('w:id'), str(1000+i))
    elements[0].append(start)
    elements[0].append(end)
    for el in elements:
        body.insert(len(body)-1, el)
for numbering in section.findall(qn('w:pgNumType')):
    section.remove(numbering)
doc.core_properties.title = 'PukSquash Developer Manual Complete Firebase Edition'
doc.save(target)
# Preserve the exact base styles, numbering, footer and package resources.
with ZipFile(target) as authored:
    replacements = {name: authored.read(name) for name in ['word/document.xml','docProps/core.xml']}
with ZipFile(sources[0]) as base:
    package = {name: base.read(name) for name in base.namelist()}
package.update(replacements)
with ZipFile(target, 'w', ZIP_DEFLATED) as final:
    for name, data in package.items():
        final.writestr(name, data)
final_doc = Document(target)
all_text = '\n'.join(p.text for p in final_doc.paragraphs)
for source in sources:
    for p in Document(source).paragraphs:
        if p.text:
            assert p.text in all_text
assert len(final_doc.sections) == 1
qa = root/'manual_work/combined_render'
qa.mkdir(exist_ok=True)
vbs = '\n'.join(['Option Explicit','Dim app, doc','Set app = CreateObject("Word.Application")','app.Visible = False','app.DisplayAlerts = 0',f'Set doc = app.Documents.Open("{target}", False, True, False)','doc.Fields.Update',f'doc.ExportAsFixedFormat "{qa / "complete.pdf"}", 17','WScript.Echo doc.ComputeStatistics(2)','doc.Close False','app.Quit'])
(root/'manual_work/render_combined.vbs').write_text(vbs,encoding='utf-8')
print(target)
print('All original section paragraphs preserved. Contents links and continuous page numbering added.')
