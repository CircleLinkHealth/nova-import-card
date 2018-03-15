# these scripts are written to speed up timezone lookups
delete from tz_timezones where tz_timezones.zone_id in (select zone_id from tz_zones where zone_name NOT like 'America/%'); #remove timezones that are not american
delete from tz_zones where zone_name NOT like 'America/%'; #remove timezone zones that are not american
delete from tz_timezones where abbreviation like '-0%'; #remove timezones that have abbreviations like -01
delete from tz_timezones where id NOT IN (select * from (select max(t.id) from tz_timezones t group by t.zone_id) x); #remove duplicate timezones (check by zone_id)