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
select e.*,i.id_inscription, i.date_inscription, i.date_annulation, i.id_niveau, i.id_au,n.nom_niveau,au.intitule, p.nom_parcours, m.id_mention, m.nom_mention, i.date_certificat_scol
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

--4-10-24 15:10
create or replace view v_liste_ue_ec as
select ue.id_unite_enseignement, ue.nom_unite_enseignement, ec.id_element_constitutif, ec.nom_element_constitutif, c.coefficient, c.id_ue_ec, epa.id_examen_par_au, se.id_session_examen, se.nom_session_examen, p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, au.id_au, au.intitule
from ue_ec_parcours_niveau_au as c
join unite_enseignement as ue on c.id_unite_enseignement = ue.id_unite_enseignement
join element_constitutif as ec on c.id_element_constitutif = ec.id_element_constitutif
join examen_par_au as epa on c.id_examen_par_au = epa.id_examen_par_au
join session_examen as se on epa.id_session_examen = se.id_session_examen
join parcours as p on c.id_parcours = p.id_parcours
join niveau as n on c.id_niveau = n.id_niveau
join au on c.id_au = au.id_au;

--11-10-24 11:04
create or replace view v_liste_ue_ec_avec_mentions as
select ue.id_unite_enseignement, ue.nom_unite_enseignement, ec.id_element_constitutif, ec.nom_element_constitutif, c.coefficient, c.id_ue_ec, epa.id_examen_par_au, se.id_session_examen, se.nom_session_examen, m.id_mention, m.nom_mention, p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, au.id_au, au.intitule
from ue_ec_parcours_niveau_au as c
    join unite_enseignement as ue on c.id_unite_enseignement = ue.id_unite_enseignement
    join element_constitutif as ec on c.id_element_constitutif = ec.id_element_constitutif
    join examen_par_au as epa on c.id_examen_par_au = epa.id_examen_par_au
    join session_examen as se on epa.id_session_examen = se.id_session_examen
    join parcours as p on c.id_parcours = p.id_parcours
    join mention as m on p.id_mention = m.id_mention
    join niveau as n on c.id_niveau = n.id_niveau
    join au on c.id_au = au.id_au;


--11-10-24 11:34
-- association de chaque au aux parcours et à leurs niveaux
create or replace view v_cj_au_parcours_niveau as
select *
from au
cross join parcours_niveau;

--comptage des étudiants par au, par parcours, par niveau
create or replace view v_nbr_etu_par_au_parcours_niveau as
select apn.id_au, apn.id_parcours, apn.id_niveau, count(id_inscription) as  nbr_inscrits
from inscription as i
join etudiants as e on i.id_etudiant = e.id_etudiants
right join v_cj_au_parcours_niveau as apn on  i.id_au = apn.id_au and i.id_niveau = apn.id_niveau and e.id_parcours = apn.id_parcours
group by apn.id_au, apn.id_parcours, apn.id_niveau


--11-10-24 12:52
--liste des ue et ec avec le nombre d'inscrits
create or replace view v_liste_ue_ec_avec_nbr_inscrits as
select l.*, n.nbr_inscrits
from v_liste_ue_ec_avec_mentions as l
join v_nbr_etu_par_au_parcours_niveau as n on l.id_parcours = n.id_parcours and l.id_niveau = n.id_niveau and l.id_au = n.id_au;

--19-10-24 10:33
-- operations (ouverture saisie notes, résulats, etc) sur examen par au
create or replace view v_operation_par_examen_par_au as
select o.*, e.id_session_examen, e.id_au
from operation_par_examen as o
join examen_par_au as e on o.id_examen_par_au = e.id_examen_par_au;

