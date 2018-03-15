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
	(select abbreviation from vw_user_timezone where user_id = calls.inbound_cpm_id) as timezone,
	window_start as call_time_start,
	window_end as call_time_end,
	(select group_concat(abbreviation SEPARATOR ',') from days_of_week where id in (select day_of_week from patient_contact_window where patient_info_id = (select id from patient_info where user_id = calls.inbound_cpm_id limit 1))) as preferred_call_days,
	(select ccm_status from patient_info where user_id = calls.inbound_cpm_id limit 1) as patient_status,
	(select display_name from practices where id = (select program_id from practice_role_user where user_id = calls.inbound_cpm_id limit 1)) as practice_name,
	(select id from practices where id = (select program_id from practice_role_user where user_id = calls.inbound_cpm_id limit 1)) as practice_id,
	(select id from users where id = (select member_user_id from patient_care_team_members where user_id = calls.inbound_cpm_id limit 1) limit 1) as billing_provider_id,
	(select display_name from users where id = (select member_user_id from patient_care_team_members where user_id = calls.inbound_cpm_id limit 1) limit 1) as billing_provider_name,
	(select birth_date from patient_info where patient_info.user_id = calls.inbound_cpm_id limit 1) as birth_date,
	`scheduler`
from calls