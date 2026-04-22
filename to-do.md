--[frontend]
in Regular Payroll Batches make it 2 tabs. separate the current active and the done batch

Active Tab | Done Tab( put the batches that has lock status here)

--[backend]

1. remove HRMO role access during draft. -DONE
2. fix issue where only pending and draft can be viewed
   2.1 fix issue in payroll controller in 2nd batch entry

3. add new position title table then connect the salary index table to it
   connect the new position title table to employees table.
   employee table <== position title table <== salary index table

4. Reports GSIS and PagIbig not working. Report Controller Error - DONE
