INSERT INTO module VALUES ({SGL_NEXT_ID}, 1, 'rate', 'Currency', 'Here you can edit and update the currency rates.', 'rate/rateadmin', 'default.png');

SELECT @moduleId := MAX(module_id) FROM module;