DROP VIEW countries_es;
CREATE VIEW countries_es AS 
(
	SELECT c.country_id, 
		ifnull(t1.tran_value, c.default_name) as tra_name,
		concat(c.default_name, ' ', c.alt_name) as alt_name,
		c.active_ind, c.relevancy
	FROM countries c
	LEFT JOIN translations t1 ON t1.object_id = c.country_id
		AND t1.object_type = 'country'
		AND t1.language_id = 'es'
		AND t1.element = 'name'
)


DROP VIEW countries_en;
CREATE VIEW countries_en AS 
(
	SELECT country_id, default_name, alt_name, active_ind, relevancy
	FROM countries
)
