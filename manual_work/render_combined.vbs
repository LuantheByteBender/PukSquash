Option Explicit
Dim app, doc
Set app = CreateObject("Word.Application")
app.Visible = False
app.DisplayAlerts = 0
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual_Complete_Firebase.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\combined_render\complete.pdf", 17
WScript.Echo doc.ComputeStatistics(2)
doc.Close False
app.Quit