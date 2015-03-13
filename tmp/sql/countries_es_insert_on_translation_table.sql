INSERT INTO translations (object_id, object_type, language_id, tran_value, element)
SELECT 
c.country_id as object_id, 
'country' as object_type, 
'es' as language_id,
pp.pai_nombre as tran_value,
'name' as element
FROM pai_pais pp INNER JOIN countries c
ON c.country_id = pp.PAI_ISO2
;