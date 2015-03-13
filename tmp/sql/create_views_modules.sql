DROP VIEW modules_en;
CREATE VIEW modules_en AS 
(
    SELECT 
        m.mod_id, m.icon, m.active, m.mod_order, m.add_on_user_registration, m.show_on_bar,
        ifnull(t1.tran_value, m.default_name) as tra_name,
        ifnull(t2.tran_value, m.default_desc) as tra_desc
    FROM modules m
    LEFT JOIN translations t1 ON t1.object_id = m.mod_id 
        AND t1.object_type = 'module'
        AND t1.language_id = 'en'
        AND t1.element = 'name'
    LEFT JOIN translations t2 ON t1.object_id = m.mod_id 
        AND t1.object_type = 'module'
        AND t1.language_id = 'en'
        AND t1.element = 'desc'
);
        

DROP VIEW modules_es;
CREATE VIEW modules_es AS 
(
    SELECT 
        m.mod_id, m.icon, m.active, m.mod_order, m.add_on_user_registration, m.show_on_bar,
        ifnull(t1.tran_value, m.default_name) as tra_name,
        ifnull(t2.tran_value, m.default_desc) as tra_desc
    FROM modules m
    LEFT JOIN translations t1 ON t1.object_id = m.mod_id 
        AND t1.object_type = 'module'
        AND t1.language_id = 'es'
        AND t1.element = 'name'
    LEFT JOIN translations t2 ON t1.object_id = m.mod_id 
        AND t1.object_type = 'module'
        AND t1.language_id = 'es'
        AND t1.element = 'desc'
)
        

