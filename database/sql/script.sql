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
-- association de chaque au aux parcours et à leurs niveaux (cross join)
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

--29-10-24 7:30
-- vérification qu'un étudiant est bien inscrit dans le parcours et le niveau auquel fait partie un element constitutif donné
create or replace view  v_check_inscription_ue_ec as
select i.*, ue_ec.id_ue_ec, coefficient, id_examen_par_au, id_unite_enseignement, id_element_constitutif
from ue_ec_parcours_niveau_au as ue_ec
join v_inscrits as i on ue_ec.id_parcours = v_inscrits.id_parcours and ue_ec.id_niveau = v_inscrits.id_niveau and ue_ec.id_au = i.id_au;


--select se.nom_session_examen, ue_ec.id_ue_ec, o.date_ouverture_verification_en_tete
--from v_liste_ue_ec as ue_ec
--join operation_par_examen as o on ue_ec.id_examen_par_au = o.id_examen_par_au
--join examen_par_au as epa on o.id_examen_par_au = epa.id_examen_par_au
--join session_examen as se on epa.id_session_examen = epa.id_session_examen;

-- 4-11-24 22:12
--liste des ue et ec avec le nombre d'inscrits vue matérialisée (à rafraichir à la fin des inscriptions )
create materialized view v_liste_ue_ec_avec_nbr_inscrits as
select l.*, n.nbr_inscrits
from v_liste_ue_ec_avec_mentions as l
join v_nbr_etu_par_au_parcours_niveau as n on l.id_parcours = n.id_parcours and l.id_niveau = n.id_niveau and l.id_au = n.id_au;

-- 6-11-24 9:48
--corrrespondance des notes et matricules
create or replace view v_correspondance_note_matricule as
select m.id_ue_ec as id_ue_ec_matricule, m.numero as numero_matricule, m.matricule, n.id_ue_ec as id_ue_ec_note, n.numero as numero_note, n.note, ue_ec.*
from barcode_note as n
full join barcode_matricule as m on n.id_ue_ec = m.id_ue_ec and n.numero = m.numero
join ue_ec_parcours_niveau_au as ue_ec on n.id_ue_ec = ue_ec.id_ue_ec or m.id_ue_ec = ue_ec.id_ue_ec;

-- 6-11-24 14:10
-- vue allégée des inscriptions (rafraichissement à la cloture des inscriptions)
create materialized view v_inscrits2 as
select i.id_au, e.id_parcours, i.id_niveau, i.id_inscription, e.id_etudiants, e.im, i.date_annulation
from inscription as i
join etudiants as e on i.id_etudiant = e.id_etudiants;


-- 6-11-24 15:28
-- récupération de tous les ec dont l'examen correspondant est une evaluation ou un repechage
create or replace view v_ue_ec_eval as
            select ue_ec.*, se.id_session_examen, nom_session_examen, type_session
            from ue_ec_parcours_niveau_au as ue_ec
            join examen_par_au as epa on ue_ec.id_examen_par_au = epa.id_examen_par_au
            join session_examen as se on epa.id_session_examen = se.id_session_examen
            where (type_session = \'eval\' or type_session = \'repe\') ;

-- filtrer le résultat de la vue pour correspondre à une seule évaluation(id_examen_par_au)
CREATE OR REPLACE FUNCTION ue_ec_eval_filtre(p_id_examen_par_au bigint)
RETURNS TABLE (id_ue_ec bigint, coefficient double precision, id_examen_par_au bigint, id_parcours bigint, id_niveau bigint, id_unite_enseignement bigint, id_element_constitutif bigint, id_au bigint, id_session_examen bigint, nom_session_examen varchar, type_session varchar) AS
$$
BEGIN
    RETURN QUERY SELECT v.id_ue_ec, v.coefficient , v.id_examen_par_au, v.id_parcours, v.id_niveau, v.id_unite_enseignement, v.id_element_constitutif, v.id_au, v.id_session_examen, v.nom_session_examen, v.type_session FROM v_ue_ec_eval as v
                    WHERE v.id_examen_par_au = p_id_examen_par_au;
END;
$$ LANGUAGE plpgsql;


-- vue de combinaison de chaque etudiant à chaque ue_ec de son parours et niveau, à chaque A.U.
create or replace function f_association_etu_ec(p_id_examen_par_au bigint)
returns table(id_au bigint, id_parcours bigint, id_niveau bigint, coefficient double precision, id_unite_enseignement bigint, id_ue_ec bigint, id_examen_par_au bigint, id_session_examen bigint, nom_session_examen varchar,  type_session varchar,id_etudiants bigint, im varchar, date_annulation date) as
$$
BEGIN
    return query select i.id_au, i.id_parcours, i.id_niveau, ue_ec.coefficient, ue_ec.id_unite_enseignement, ue_ec.id_ue_ec, ue_ec.id_examen_par_au, ue_ec.id_session_examen, ue_ec.nom_session_examen, ue_ec.type_session,i.id_etudiants, i.im, i.date_annulation
                    from ue_ec_eval_filtre(p_id_examen_par_au) as ue_ec
                    left join v_inscrits2 as i on i.id_parcours = ue_ec.id_parcours and i.id_niveau =  ue_ec.id_niveau and i.id_au = ue_ec.id_au;

end;
$$ LANGUAGE plpgsql;

 -- 6-11-24 15:44
 -- association des combinaisons ue_ec-etu avec les notes enregistrees
create or replace function f_note(p_id_examen_par_au bigint)
returns table(id_au bigint, id_parcours bigint, id_niveau bigint, id_unite_enseignement bigint, id_ue_ec bigint, id_examen_par_au bigint, id_session_examen bigint, nom_session_examen varchar, type_session varchar, im varchar, id_etudiants bigint, note double precision) as
$$
begin
    return query select a.id_au, a.id_parcours, a.id_niveau, a.id_unite_enseignement, a.id_ue_ec, a.id_examen_par_au, a.id_session_examen, a.nom_session_examen, a.type_session, a.im, a.id_etudiants,coalesce(n.note, 0) as note
                    from v_correspondance_note_matricule as n
                    right join f_association_etu_ec(p_id_examen_par_au) as a on a.id_ue_ec = n.id_ue_ec and a.im = n.matricule;
end;
$$ language plpgsql;



 --6-11-24 22:13
-- moyenne des UE
create or replace view v_note_moyenne_ue as
            select id_au, id_parcours, id_niveau, coefficient, id_unite_enseignement, id_examen_par_au, id_session_examen, im, id_etudiants,avg(note) as note
            from v_note
            group by id_au, id_parcours, id_niveau, coefficient, id_unite_enseignement, id_examen_par_au, id_session_examen, im, id_etudiants
-- 6-11-24 22:32
create or replace view v_note_moyenne as
select id_au, id_parcours, id_niveau, id_examen_par_au, im, id_etudiants, sum(coefficient) as total_coefficient, sum(note*coefficient) as total, (sum(note*coefficient)/sum(coefficient)) as moyenne
from v_note_moyenne_ue
group by id_au, id_parcours, id_niveau, id_examen_par_au, im, id_etudiants;


