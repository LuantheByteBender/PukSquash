Option Explicit
Dim app, doc
Set app = CreateObject("Word.Application")
app.Visible = False
app.DisplayAlerts = 0
Set doc = app.Documents.Open("C:\Users\300w12\Downloads\Campus Marketplace - Developer Manual (4).docx", False, True, False)
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\reference_render\reference.pdf", 17
WScript.Echo "Pages " & doc.ComputeStatistics(2)
doc.Close False
app.Quit
