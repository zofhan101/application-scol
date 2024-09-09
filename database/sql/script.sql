-- 27-08-24 11:22
-- création de la vue des utilisateurs valides

create or replace view users_valides as
select users.*
from users 
join role on users.id_role = role.id
    where date_suppr is null or nom_role = 'admin';