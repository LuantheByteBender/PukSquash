Option Explicit
Dim app, doc
Set app = CreateObject("Word.Application")
app.Visible = False
app.DisplayAlerts = 0
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\00_Project_Setup_and_Firebase_Data_Layer.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\00_Project_Setup_and_Firebase_Data_Layer.pdf", 17
WScript.Echo "00_Project_Setup_and_Firebase_Data_Layer.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\01_Registration_Login_and_Access_Control.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\01_Registration_Login_and_Access_Control.pdf", 17
WScript.Echo "01_Registration_Login_and_Access_Control.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\02_User_Profile_Management.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\02_User_Profile_Management.pdf", 17
WScript.Echo "02_User_Profile_Management.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\03_Court_Availability_Grid.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\03_Court_Availability_Grid.pdf", 17
WScript.Echo "03_Court_Availability_Grid.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\04_Court_Booking_Creation.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\04_Court_Booking_Creation.pdf", 17
WScript.Echo "04_Court_Booking_Creation.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\05_Joining_an_Open_Booking.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\05_Joining_an_Open_Booking.pdf", 17
WScript.Echo "05_Joining_an_Open_Booking.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\06_My_Bookings_History_and_Cancellation.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\06_My_Bookings_History_and_Cancellation.pdf", 17
WScript.Echo "06_My_Bookings_History_and_Cancellation.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\07_Coach_Availability_and_Notice_Board.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\07_Coach_Availability_and_Notice_Board.pdf", 17
WScript.Echo "07_Coach_Availability_and_Notice_Board.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\08_Private_Coaching_Schedule.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\08_Private_Coaching_Schedule.pdf", 17
WScript.Echo "08_Private_Coaching_Schedule.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\09_Team_Practice_Bookings.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\09_Team_Practice_Bookings.pdf", 17
WScript.Echo "09_Team_Practice_Bookings.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\10_Administrator_User_Management.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\10_Administrator_User_Management.pdf", 17
WScript.Echo "10_Administrator_User_Management.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\11_Administrator_Court_Management.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\11_Administrator_Court_Management.pdf", 17
WScript.Echo "11_Administrator_Court_Management.docx " & doc.ComputeStatistics(2)
doc.Close False
Set doc = app.Documents.Open("C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\output\PukSquash_Developer_Manual\12_Registration_Payments_and_Firebase_Extension.docx", False, True, False)
doc.Fields.Update
doc.ExportAsFixedFormat "C:\Users\300w12\Desktop\Luan Persoonlik\Dev\PukSquash\PukSquash\manual_work\final_render\12_Registration_Payments_and_Firebase_Extension.pdf", 17
WScript.Echo "12_Registration_Payments_and_Firebase_Extension.docx " & doc.ComputeStatistics(2)
doc.Close False
app.Quit