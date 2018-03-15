drop view if exists vw_calls;

create view vw_calls as 
select 
	id, 
	inbound_cpm_id as patient_id,
	(select display_name from users where users.id = calls.inbound_cpm_id) as patient_name,
	outbound_cpm_id as nurse_id,
	(select display_name from users where users.id = calls.outbound_cpm_id) as nurse_name,
	scheduled_date,
	(select last_contact_time from patient_info where patient_info.user_id = calls.inbound_cpm_id limit 1) as last_call,
	(select cur_month_activity_time from patient_info where patient_info.user_id = calls.inbound_cpm_id limit 1) as ccm_time,
	(select no_of_successful_calls from patient_monthly_summaries where patient_monthly_summaries.patient_id = calls.inbound_cpm_id order by id desc limit 1) as no_of_successful_calls,
	(select abbreviation from vw_user_timezone where user_id = calls.inbound_cpm_id) as timezone
from calls limit 10