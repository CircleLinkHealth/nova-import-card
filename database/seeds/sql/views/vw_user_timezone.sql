drop view if exists vw_user_timezone;

create view vw_user_timezone as
select 
	id as user_id,
	timezone as name,
	(select abbreviation from tz_timezones where zone_id = ((select zone_id from tz_zones where tz_zones.zone_name = users.timezone limit 1)) order by tz_timezones.time_start desc limit 1) as abbreviation
from users;