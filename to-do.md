--[frontend]
in Regular Payroll Batches make it 2 tabs. separate the current active and the done batch

Active Tab | Done Tab( put the batches that has lock status here)

- Fix duplicated multiple success message banners in Payroll Batch, After pulling attendance, even in accountant certifying the batch gives duplicated messages too

-add proper model pop ups for confirmations messages

- http://localhost:8000/my-payslip My Payslip should fetch the relevant data, should also create table for that info to be displayed or other way to display.

-http://localhost:8000/special-payroll/differential/create when creating salary differential we should have 3 types of process, whether addition, deduction, or tax .


--[backend]
in reports the Grand Total (GSIS) the math is not mathing. when "Both(Full Month)" filter is selected, it adds both 1st cut off and 2nd cutoff regardless if the other one didnt have any report for that period
