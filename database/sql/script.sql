-- 27-08-24 11:22
-- création de la vue des utilisateurs valides

create or replace view users_valides as
select users.*
from users
join role on users.id_role = role.id
    where date_suppr is null or nom_role = 'admin';


-- 12-08-24 11:03
--création de la vue des selectionnes avec leur parcours
create or replace view v_selectionnes_parcours as
select s.*, p.nom_parcours
from selectionnes as s
join parcours as p on s.id_parcours = p.id_parcours;

--17-09-24 15:00
-- vue des étudiants inscrits
create or replace view  v_inscrits as
select e.nom,n.nom_niveau,au.intitule
from etudiants as e
join inscription as i on i.id_etudiants = e.id_etudiants
join niveau as n on i.id_niveau = n.id_niveau
join au on i.id_au = au.id_au
where i.date_annulation is null
