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
select e.*,i.id_inscription, i.date_inscription, i.date_annulation, i.id_niveau, i.id_au,n.nom_niveau,au.intitule
from etudiants as e
join inscription as i on i.id_etudiant = e.id_etudiants
join niveau as n on i.id_niveau = n.id_niveau
join au on i.id_au = au.id_au;


--20-09-24 09:23
create or replace view  v_inscrits as
select e.*,i.id_inscription, i.date_inscription, i.date_certificat_scol, i.date_annulation, i.id_niveau, i.id_au,n.nom_niveau,au.intitule, p.nom_parcours, m.id_mention, m.nom_mention
from etudiants as e
join inscription as i on i.id_etudiant = e.id_etudiants
join niveau as n on i.id_niveau = n.id_niveau
join au on i.id_au = au.id_au
join parcours as p on e.id_parcours =  p.id_parcours
join mention as m on p.id_mention = m.id_mention;

--26-09-24 20:02
create or replace view v_parcours_niveau as
select p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, n.rang
from parcours as p
join parcours_niveau as pn on pn.id_parcours = p.id_parcours
join niveau as n on pn.id_niveau = n.id_niveau;
