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

select nom_unite_enseignement, id_ue_ec
from v_liste_ue_ec
where id_au = 4 and id_parcours = 4 and id_niveau = 2 and id_session_examen = 2
order by id_unite_enseignement asc, id_ue_ec asc;

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


--
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
            where type_session = \'eval\' or type_session = \'repe\';

-- vue d'association de chaque etudiant à chaque ue_ec de son parours et niveau à chaque A.U.
create or replace view v_association_etu_ec as
            select ue_ec.id_au, ue_ec.id_parcours, ue_ec.id_niveau, ue_ec.coefficient, id_unite_enseignement, id_ue_ec, ue_ec.id_element_constitutif, ue_ec.id_examen_par_au, id_session_examen, nom_session_examen, type_session,id_etudiants, im, date_annulation
            from v_ue_ec_eval as ue_ec
            left join v_inscrits2 as i on i.id_parcours = ue_ec.id_parcours and i.id_niveau =  ue_ec.id_niveau and i.id_au = ue_ec.id_au;

 -- 6-11-24 15:44
 -- association des combinaisons ue_ec-etu avec les notes enregistrees
 create or replace view v_note as
 select a.id_au, a.id_parcours, a.id_niveau, a.coefficient, a.id_unite_enseignement, a.id_ue_ec, a.id_element_constitutif, a.id_examen_par_au, id_session_examen, nom_session_examen, type_session, a.im, a.id_etudiants,coalesce(n.note, 0) as note, date_annulation
 from v_correspondance_note_matricule as n
 right join v_association_etu_ec as a on a.id_ue_ec = n.id_ue_ec and a.im = n.matricule;

 --6-11-24 22:13
-- moyenne des UE
create or replace view v_note_moyenne_ue as
            select id_au, id_parcours, id_niveau, coefficient, id_unite_enseignement, id_examen_par_au, id_session_examen, im, id_etudiants,avg(note) as note, date_annulation
            from v_note
            group by id_au, id_parcours, id_niveau, coefficient, id_unite_enseignement, id_examen_par_au, id_session_examen, im, id_etudiants, date_annulation

-- 7-11-24 11:35
-- mention validé , non validé ou éliminatoire dans le résultat
create or replace view v_note_validation_ue as
        select id_au, id_parcours, id_niveau, id_examen_par_au, id_session_examen, coefficient, id_unite_enseignement, date_annulation,im, id_etudiants,note,
            case
                when note >= (select note_validation_ue as moyenne from note_validation_ue order by id_note_validation_ue desc limit 1) then 'V'
                when note > (select note_elim from note_eliminatoire order by id_note_eliminatoire desc limit 1) and note < (select note_validation_ue from note_validation_ue order by id_note_validation_ue desc limit 1) then 'N'
                else 'E'
            end AS valide
        from v_note_moyenne_ue


-- 7-11-24 12:49
-- assemblage des ue avec leurs ec respectifs
create or replace view v_note_validation_ue_avec_ec as
    select n.id_au, n.id_parcours, n.id_niveau, n.id_examen_par_au, n.id_session_examen, n.nom_session_examen, n.type_session, n.date_annulation, n.coefficient, n.id_unite_enseignement, n.id_ue_ec, n.id_element_constitutif, n.im, n.id_etudiants, n.note as note_ec, v.note as note_ue, v.valide
    from v_note_validation_ue as v
    join v_note as n on (n.id_au = v.id_au and n.id_parcours = v.id_parcours and n.id_niveau = v.id_niveau and n.id_unite_enseignement = v.id_unite_enseignement and n.id_examen_par_au = v.id_examen_par_au and n.id_etudiants = v.id_etudiants) or (n.id_au = v.id_au and n.id_parcours = v.id_parcours and n.id_niveau = v.id_niveau and n.id_unite_enseignement = v.id_unite_enseignement and n.id_examen_par_au = v.id_examen_par_au and n.id_etudiants is null and v.id_etudiants is null);

-- creation de la table NOTE_EVAL: remplie avec la vue  v_note_validation_ue_avec_ec


-- 7-11-24 17:23
create or replace view v_note_eval_ue as
select distinct on( id_au, id_parcours, id_niveau, id_examen_par_au,id_unite_enseignement,im)id_note_eval, id_au, id_parcours, id_niveau, id_examen_par_au,id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, id_unite_enseignement, im, id_etudiants, note_ue, valide
from note_eval
order by id_au, id_parcours, id_niveau, id_examen_par_au,id_unite_enseignement,im, id_ue_ec asc


-- 8-11-24 9:44
-- jointure avec les tables pour ajouter les libeles aux resultats
create or replace view v_note_eval_ue_complet as
select  id_note_eval, au.id_au, au.intitule, p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, id_examen_par_au,id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, ue.id_unite_enseignement, ue.nom_unite_enseignement, v.im, e.id_etudiants, e.nom, e.prenoms, note_ue, valide
from v_note_eval_ue as v
join au on v.id_au = au.id_au
join parcours as p on v.id_parcours = p.id_parcours
join niveau as n on v.id_niveau = n.id_niveau
join unite_enseignement as ue on v.id_unite_enseignement = ue.id_unite_enseignement
left join etudiants as e on v.id_etudiants = e.id_etudiants;

-- génération de l'affichage de réusltats par au,parcours,niveau, session d'examen
create or replace function creer_v_resultats_eval(a_id_au bigint, a_id_parcours bigint, a_id_niveau bigint, a_id_examen_par_au bigint)
RETURNS void AS
$$
DECLARE
    nom_ue RECORD;
    requete TEXT := 'CREATE OR REPLACE VIEW v_resultats_eval AS SELECT ROW_NUMBER() OVER (ORDER BY NULL) AS n° ,im, nom, prenoms';
    colonnes TEXT := '';
BEGIN
    FOR nom_ue IN
        select distinct nom_unite_enseignement
        from v_note_eval_ue_complet as v
        where v.id_au = a_id_au
            and v.id_parcours = a_id_parcours
            and v.id_niveau = a_id_niveau
            and v.id_examen_par_au = a_id_examen_par_au
    LOOP
        colonnes := colonnes ||
            ', Max(CASE WHEN nom_unite_enseignement = ''' || nom_ue.nom_unite_enseignement || ''' THEN valide END) AS \"Résultats ' || nom_ue.nom_unite_enseignement || '\"';
    END LOOP;

    requete := requete || colonnes || ' FROM  v_note_eval_ue_complet WHERE id_au = ' || a_id_au || ' and id_parcours = ' || a_id_parcours || ' and id_niveau = ' || a_id_niveau || ' and id_examen_par_au = ' || a_id_examen_par_au || ' GROUP BY im, nom, prenoms;' ;

    EXECUTE 'DROP view if exists v_resultats_eval;' ;
    RAISE NOTICE '%',requete;
    EXECUTE requete;


END;
$$ LANGUAGE plpgsql;


-- AFFICHAGE EN  COMPLET DES RESULTATS DES EVALUATIONS: NOTE, NOTES_EC, VALIDATION
-- 13-11-24 8:06
create or replace view v_note_eval_complet as
    select  id_note_eval, au.id_au, au.intitule, p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, id_examen_par_au,id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, ue.id_unite_enseignement, ue.nom_unite_enseignement,  note.id_ue_ec, ec.id_element_constitutif, ec.nom_element_constitutif,e.im, e.id_etudiants, e.nom, e.prenoms, note_ec, note_ue, valide
    from note_eval as note
    join au on note.id_au = au.id_au
    join parcours as p on note.id_parcours = p.id_parcours
    join niveau as n on note.id_niveau = n.id_niveau
    join unite_enseignement as ue on note.id_unite_enseignement = ue.id_unite_enseignement
    join element_constitutif as ec on note.id_element_constitutif = ec.id_element_constitutif
    left join etudiants as e on note.id_etudiants = e.id_etudiants;



--11-11-24 23:09
-- APPLICATION DES CONDITIONS DE PASSAGE
-- vue des totaux des notes
create or replace view v_total_note as
select id_au, id_parcours, id_niveau,  date_annulation_inscription, im, id_etudiants, sum(note_ue) as total, sum(coefficient) as total_coefficient
from v_note_eval_ue
where type_session = 'eval'
group by id_au, id_parcours, id_niveau,  date_annulation_inscription, im, id_etudiants;

-- vue des moyennes
create or replace view v_moyennes as
select id_au, id_parcours, id_niveau, date_annulation_inscription, im, id_etudiants,total, total_coefficient, (total/total_coefficient) as moyenne
from v_total_note;

-- 12-11-24 8:51

-- liste des ue par AU, parcours, niveau dont les examens correspondant sont des evaluations
create or replace view v_liste_ue as
select distinct on (ue_ec.id_au, id_parcours, id_niveau, id_unite_enseignement) ue_ec.id_au, id_parcours, id_niveau, id_unite_enseignement
from ue_ec_parcours_niveau_au as ue_ec
join examen_par_au as epa on ue_ec.id_examen_par_au = epa.id_examen_par_au
join session_examen as se on epa.id_session_examen = se.id_session_examen
where type_session = 'eval'
order by ue_ec.id_au desc, id_parcours, id_niveau, id_unite_enseignement, id_ue_ec asc;

-- calcul du nombre d'UE par année
create or replace view v_nombre_ue as
select id_au, id_parcours, id_niveau, count(id_unite_enseignement) as nombre_ue
from v_liste_ue
group by id_au, id_parcours, id_niveau;

-- nombre des ue_validees
create or replace view v_nombre_ue_validees as
select id_au, id_parcours, id_niveau, im, id_etudiants, count(valide) as nombre_ue_validees
from v_note_eval_ue
where valide = 'V' and type_session = 'eval'
group by id_au, id_parcours, id_niveau, im, id_etudiants;

-- cas des parcours et niveaux sans inscrits
create or replace view v_nombre_ue_validees_sans_inscrits as
select distinct on(id_au, id_parcours, id_niveau) id_au, id_parcours, id_niveau, im, id_etudiants, 0 as nombre_ue_validees
from v_note_eval_ue
where id_etudiants is null and type_session = 'eval'
order by id_au, id_parcours, id_niveau, id_examen_par_au asc ,id_unite_enseignement asc;



-- 12-11-24 10:38
-- assemblage des deux nombres d'UE validées
create or replace view v_nombre_ue_validees_complet as
select * from v_nombre_ue_validees
union all
select * from  v_nombre_ue_validees_sans_inscrits;

--12-11-24 11:51
-- taux des UE validee
create or replace view v_taux_ue_validees as
select v.id_au, v.id_parcours, v.id_niveau, im, id_etudiants, nombre_ue, nombre_ue_validees, ((nombre_ue_validees::DOUBLE PRECISION/nombre_ue::DOUBLE PRECISION)*100) as pourcentage_validation
from v_nombre_ue_validees_complet as v
join v_nombre_ue as n on v.id_au = n.id_au and v.id_parcours = n.id_parcours and v.id_niveau = n.id_niveau;

--12-11-24 13:22
-- nombre d'UE avec notes eliminatoires
create or replace view v_nombre_note_eliminatoire as
select id_au, id_parcours, id_niveau, im, id_etudiants, count(valide) as nombre_note_eliminatoire
from v_note_eval_ue
where valide = 'E' and type_session = 'eval'
group by id_au, id_parcours, id_niveau, im, id_etudiants;

-- résultats de l'addition des évaluations au long de l'année avant le repechage
create or replace view v_resultats as
    select m.id_au, m.id_parcours, m.id_niveau, m.id_etudiants, m.im, total, total_coefficient, moyenne, nombre_ue,  nombre_ue_validees, pourcentage_validation, nombre_note_eliminatoire
    from v_moyennes as m
    join v_taux_ue_validees as v on (m.id_au = v.id_au and m.id_parcours = v.id_parcours and m.id_niveau = v.id_niveau and m.id_etudiants = v.id_etudiants) or (m.id_au = v.id_au and m.id_parcours = v.id_parcours and m.id_niveau = v.id_niveau and m.id_etudiants is null and  v.id_etudiants is null )
    join v_nombre_note_eliminatoire as e on (m.id_au = e.id_au and m.id_parcours = e.id_parcours and m.id_niveau = e.id_niveau and m.id_etudiants = e.id_etudiants) or (m.id_au = e.id_au and m.id_parcours = e.id_parcours and m.id_niveau = e.id_niveau and m.id_etudiants is null and e.id_etudiants is null);


--select dense_rank() over(order by moyenne desc) as rang, im, moyenne, pourcentage_validation, nombre_note_eliminatoire
--    from v_resultats
--    where id_au = 4 and id_parcours = 4 and id_niveau = 2;

-- première décision en fonction du résultat
create or replace view v_decision as
    select id_au, id_parcours, id_niveau, id_etudiants, im, total, total_coefficient, moyenne, nombre_ue,  nombre_ue_validees, pourcentage_validation, nombre_note_eliminatoire,
    CASE
        WHEN
            moyenne >= (select moyenne_admission from moyenne_admission order by id_moyenne_admission desc limit 1)
            AND pourcentage_validation >= (select pourcentage_admission from pourcentage_admission order by id_pourcentage_admission desc limit 1)
            AND nombre_note_eliminatoire = 0
        THEN  'admis'::varchar
        ELSE 'repechage'::varchar
    END AS decision
    from v_resultats;


--select dense_rank() over(order by moyenne desc) as rang, im, moyenne, pourcentage_validation, nombre_note_eliminatoire, decision
--    from v_decision
--    where id_au = 4 and id_parcours = 4 and id_niveau = 2;


-- vue complète avec les notes des ue, ec et la moyenne
create or replace view v_resultats_avec_notes as
    select n.*, total, total_coefficient, moyenne, nombre_ue, nombre_ue_validees, pourcentage_validation, nombre_note_eliminatoire, decision
    from v_decision as d
    join note_eval as n on (d.id_au = n.id_au and d.id_parcours = n.id_parcours and d.id_niveau = n.id_niveau and d.id_etudiants = n.id_etudiants) or (d.id_au = n.id_au and d.id_parcours = n.id_parcours and d.id_niveau = n.id_niveau and d.id_etudiants is null and n.id_etudiants is null);

-- CREATION DE LA TABLE RESULTATS_AVANT_REPECHAGE

-- vue des resultats annuels avec tous les libellés (VM RAFRAICHIE APRES LA GENERATION DES RESULTATS ANNUELS AVANT REPECHAGE)
create materialized view v_resultats_avant_repechage_complet as
    select id_resultats_avant_repechage, id_note_eval, au.id_au, au.intitule, p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, ue.id_unite_enseignement, ue.nom_unite_enseignement, id_ue_ec, ec.id_element_constitutif, ec.nom_element_constitutif, e.id_etudiants, e.im, e.nom, e.prenoms, note_ec, note_ue, valide,  total, total_coefficient, moyenne, nombre_ue, nombre_ue_validees, pourcentage_validation, nombre_note_eliminatoire, decision
    from resultats_avant_repechage as r
    join au on r.id_au = au.id_au
    join parcours as p on r.id_parcours = p.id_parcours
    join niveau as n on r.id_niveau = n.id_niveau
    join unite_enseignement as ue on r.id_unite_enseignement = ue.id_unite_enseignement
    join element_constitutif as ec on r.id_element_constitutif = ec.id_element_constitutif
    left join etudiants as e on r.id_etudiants = e.id_etudiants;


    --select DENSE_RANK() OVER (ORDER BY moyenne desc) AS rang , im, nom_unite_enseignement, note_ec, note_ue, moyenne, decision from v_resultats_avant_repechage_complet
    --        where id_au = 4 and id_parcours = 4 and id_niveau = 2
    --        order by rang asc, id_session_examen asc, id_unite_enseignement asc , id_element_constitutif asc


-- LISTE DE REPECHAGE
-- vue des candidats qui devront subir les épreuves de repêchage (VM RAFRAICHIE APRES LA GENERATION DES RESULTATS ANNUELS AVANT REPECHAGE)
create materialized view v_liste_repechage as
    select distinct on (id_au, id_parcours, id_niveau, id_etudiants, id_unite_enseignement) id_au, intitule, id_parcours, nom_parcours, id_niveau, nom_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session, id_unite_enseignement, nom_unite_enseignement, id_etudiants, im, nom, prenoms, valide, decision
    from v_resultats_avant_repechage_complet
    where decision = 'repechage'
    order by id_au, id_parcours, id_niveau, id_etudiants, id_unite_enseignement, id_ue_ec asc;

-- création de l'affichage de la liste de repechage
create or replace function creer_v_liste_repechage_affichage(a_id_au bigint, a_id_parcours bigint, a_id_niveau bigint)
RETURNS void AS
$$
DECLARE
    nom_ue RECORD;
    requete TEXT := 'CREATE MATERIALIZED VIEW v_liste_repechage_affichage AS SELECT ROW_NUMBER() OVER (ORDER BY NULL) AS N° ,im, nom, prenoms';
    colonnes TEXT := '';
BEGIN
    FOR nom_ue IN
        select distinct nom_unite_enseignement
        from v_liste_repechage as v
        where v.id_au = a_id_au
            and v.id_parcours = a_id_parcours
            and v.id_niveau = a_id_niveau
    LOOP
        colonnes := colonnes ||
            ', Max(CASE WHEN nom_unite_enseignement = ''' || nom_ue.nom_unite_enseignement || ''' THEN valide END) AS \"Résultats ' || nom_ue.nom_unite_enseignement || '\"';
    END LOOP;

    requete := requete || colonnes || ' FROM  v_liste_repechage WHERE id_au = ' || a_id_au || ' and id_parcours = ' || a_id_parcours || ' and id_niveau = ' || a_id_niveau ||  ' GROUP BY im, nom, prenoms;' ;

    EXECUTE 'DROP MATERIALIZED VIEW  if exists v_liste_repechage_affichage;' ;
    RAISE NOTICE '%',requete;
    EXECUTE requete;


END;
$$ LANGUAGE plpgsql;




