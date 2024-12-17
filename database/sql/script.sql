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
-- TRANSFORMEE EN VUE MATERIALISEE RAFRAICHIE  APRES LES INSCRIPTIONS ET LA DEFINITION DES UE ET EC
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


-- ******** ---

-- GESTION DES NOTES

-- ******** ---

    -- ******** ---

    -- GESTION DES NOTES
    -- NOTES DES EVALUATIONS INDIVIDUELLES

    -- ******** ---


-- 4-11-24 22:12
--liste des ue et ec avec le nombre d'inscrits vue matérialisée (à rafraichir à la fin des inscriptions ET LA DEFINITION DES UE ET EC )
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
select i.id_au, e.id_parcours, i.id_niveau, i.id_inscription, e.id_etudiants, e.im, i.date_annulation, statut, a_passe_examen
from inscription as i
join etudiants as e on i.id_etudiant = e.id_etudiants;


-- 6-11-24 15:28
-- récupération de tous les ec dont l'examen correspondant est une evaluation ou un repechage (pour exclure les matière du concours PACES)
create or replace view v_ue_ec_eval as
            select ue_ec.*, se.id_session_examen, nom_session_examen, type_session
            from ue_ec_parcours_niveau_au as ue_ec
            join examen_par_au as epa on ue_ec.id_examen_par_au = epa.id_examen_par_au
            join session_examen as se on epa.id_session_examen = se.id_session_examen
            where type_session = \'eval\' or type_session = \'repe\';

-- vue d'association de chaque etudiant à chaque ue_ec de son parours et niveau à chaque A.U.
create or replace view v_association_etu_ec as
            select ue_ec.id_au, ue_ec.id_parcours, ue_ec.id_niveau, ue_ec.coefficient, id_unite_enseignement, id_ue_ec, ue_ec.id_element_constitutif, ue_ec.id_examen_par_au, id_session_examen, nom_session_examen, type_session,id_etudiants, im, date_annulation, statut, a_passe_examen
            from v_ue_ec_eval as ue_ec
            left join v_inscrits2 as i on i.id_parcours = ue_ec.id_parcours and i.id_niveau =  ue_ec.id_niveau and i.id_au = ue_ec.id_au;

 -- 6-11-24 15:44
 -- association des combinaisons ue_ec-etu avec les notes enregistrees
 create or replace view v_note as
 select a.id_au, a.id_parcours, a.id_niveau, a.coefficient, a.id_unite_enseignement, a.id_ue_ec, a.id_element_constitutif, a.id_examen_par_au, id_session_examen, nom_session_examen, type_session, a.im, a.id_etudiants,coalesce(n.note, 0) as note, date_annulation, statut, a_passe_examen
 from v_correspondance_note_matricule as n
 right join v_association_etu_ec as a on a.id_ue_ec = n.id_ue_ec and a.im = n.matricule;

 --6-11-24 22:13
-- moyenne des UE
create or replace view v_note_moyenne_ue as
            select id_au, id_parcours, id_niveau, coefficient, id_unite_enseignement, id_examen_par_au, id_session_examen, im, id_etudiants,avg(note) as note, date_annulation
            from v_note
            group by id_au, id_parcours, id_niveau, coefficient, id_unite_enseignement, id_examen_par_au, id_session_examen, im, id_etudiants, date_annulation

-- 18-11-24 16:17
-- ******** ---
    --MODIFACATION DU CALCUL DE NOTE MOYENNE D'UNE UE
 -- ******** ---

create or replace view v_note_moyenne_ue as
            select id_au, id_parcours, id_niveau, coefficient, id_unite_enseignement, id_examen_par_au, id_session_examen, im, id_etudiants,avg(note) as note, date_annulation
            from v_note
            WHERE type_session = 'eval'
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

--select intitule, nom_parcours, nom_niveau, nom_unite_enseignement, im, note_ue, valide
--from v_note_eval_ue_complet;

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
-- ******** ---

    -- GESTION DES NOTES
    -- NOTES DES EVALUATIONS INDIVIDUELLES
    -- APPLICATION DES CONDITIONS DE PASSAGE

 -- ******** ---

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

    --select intitule, nom_parcours, nom_niveau, im, moyenne
    --from v_moyennes as v
    --join au on v.id_au = au.id_au
    --join parcours as p on v.id_parcours = p.id_parcours
    --join niveau as n on v.id_niveau = n.id_niveau;



-- 12-11-24 8:51

-- liste des ue par AU, parcours, niveau dont les examens correspondant sont des evaluations
create or replace view v_liste_ue as
select distinct on (ue_ec.id_au, id_parcours, id_niveau, id_unite_enseignement) ue_ec.id_au, id_parcours, id_niveau, id_unite_enseignement
from ue_ec_parcours_niveau_au as ue_ec
join examen_par_au as epa on ue_ec.id_examen_par_au = epa.id_examen_par_au
join session_examen as se on epa.id_session_examen = se.id_session_examen
where type_session = 'eval'
order by ue_ec.id_au desc, id_parcours, id_niveau, id_unite_enseignement, id_ue_ec asc;

-- ******** --
-- MODIFICATION v_liste_ue
-- 19-11-24 13:54
create or replace view v_liste_ue as
select distinct on (ue_ec.id_au, id_parcours, id_niveau, id_unite_enseignement, epa.id_examen_par_au) ue_ec.id_au, id_parcours, id_niveau, id_unite_enseignement
from ue_ec_parcours_niveau_au as ue_ec
join examen_par_au as epa on ue_ec.id_examen_par_au = epa.id_examen_par_au
join session_examen as se on epa.id_session_examen = se.id_session_examen
where type_session = 'eval'
order by ue_ec.id_au desc, id_parcours, id_niveau, id_unite_enseignement, epa.id_examen_par_au,id_ue_ec asc;


-- ******** --

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

-- deplacé dans cette section le 3-12-24 14:33
-- nombre d'ue à valider par au, parcours, niveau
    create or replace view v_nombre_ue_a_valider as
        select id_au, id_parcours, id_niveau, nombre_ue, FLOOR((nombre_ue * (select pourcentage_admission from pourcentage_admission order by id_pourcentage_admission desc limit 1))/100) AS nombre_ue_a_valider
        from v_nombre_ue;


--12-11-24 13:22
-- nombre d'UE avec notes eliminatoires
create or replace view v_nombre_note_eliminatoire as
select id_au, id_parcours, id_niveau, im, id_etudiants, count(valide) as nombre_note_eliminatoire
from v_note_eval_ue
where valide = 'E' and type_session = 'eval'
group by id_au, id_parcours, id_niveau, im, id_etudiants;

-- résultats de l'addition des évaluations au long de l'année avant le repechage
create or replace view v_resultats as
    select m.id_au, m.id_parcours, m.id_niveau, m.id_etudiants, m.im, total, total_coefficient, moyenne, nombre_ue,  nombre_ue_validees, nombre_ue_a_valider, nombre_note_eliminatoire
    from v_moyennes as m
    join v_nombre_ue_a_valider as v on (m.id_au = v.id_au and m.id_parcours = v.id_parcours and m.id_niveau = v.id_niveau )
    join v_nombre_note_eliminatoire as e on (m.id_au = e.id_au and m.id_parcours = e.id_parcours and m.id_niveau = e.id_niveau and m.id_etudiants = e.id_etudiants) or (m.id_au = e.id_au and m.id_parcours = e.id_parcours and m.id_niveau = e.id_niveau and m.id_etudiants is null and e.id_etudiants is null)
    join v_nombre_ue_validees_complet as c on (m.id_au = c.id_au and m.id_parcours = c.id_parcours and m.id_niveau = c.id_niveau and m.id_etudiants = c.id_etudiants) or (m.id_au = c.id_au and m.id_parcours = c.id_parcours and m.id_niveau = c.id_niveau and m.id_etudiants is null and c.id_etudiants is null);


--select dense_rank() over(order by moyenne desc) as rang, im, moyenne, pourcentage_validation, nombre_note_eliminatoire
--    from v_resultats
--    where id_au = 4 and id_parcours = 4 and id_niveau = 2;

-- première décision en fonction du résultat
create or replace view v_decision as
    select id_au, id_parcours, id_niveau, id_etudiants, im, total, total_coefficient, moyenne, nombre_ue,  nombre_ue_validees, nombre_ue_a_valider, nombre_note_eliminatoire,
    CASE
        WHEN
            moyenne >= (select moyenne_admission from moyenne_admission order by id_moyenne_admission desc limit 1)
            AND nombre_ue_validees >= nombre_ue_a_valider
            AND nombre_note_eliminatoire = 0
        THEN  'admis'::varchar
        ELSE 'repechage'::varchar
    END AS decision
    from v_resultats;


    --select intitule, nom_parcours, nom_niveau, im, moyenne, decision
    --from v_decision as v
    --join au on v.id_au = au.id_au
    --join parcours as p on v.id_parcours = p.id_parcours
    --join niveau as n on v.id_niveau = n.id_niveau


--select dense_rank() over(order by moyenne desc) as rang, im, moyenne, pourcentage_validation, nombre_note_eliminatoire, decision
--    from v_decision
--    where id_au = 4 and id_parcours = 4 and id_niveau = 2;


-- vue complète avec les notes des ue, ec et la moyenne
create or replace view v_resultats_avec_notes as
    select n.*, total, total_coefficient, moyenne, nombre_ue, nombre_ue_validees, nombre_ue_a_valider, nombre_note_eliminatoire, decision
    from v_decision as d
    join note_eval as n on (d.id_au = n.id_au and d.id_parcours = n.id_parcours and d.id_niveau = n.id_niveau and d.id_etudiants = n.id_etudiants) or (d.id_au = n.id_au and d.id_parcours = n.id_parcours and d.id_niveau = n.id_niveau and d.id_etudiants is null and n.id_etudiants is null);

-- CREATION DE LA TABLE RESULTATS_AVANT_REPECHAGE

-- vue des resultats annuels avec tous les libellés (VM RAFRAICHIE APRES LA GENERATION DES RESULTATS ANNUELS AVANT REPECHAGE)
create materialized view v_resultats_avant_repechage_complet as
    select id_resultats_avant_repechage, id_note_eval, au.id_au, au.intitule, p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session, date_annulation_inscription, coefficient, ue.id_unite_enseignement, ue.nom_unite_enseignement, id_ue_ec, ec.id_element_constitutif, ec.nom_element_constitutif, e.id_etudiants, e.im, e.nom, e.prenoms, note_ec, note_ue, valide,  total, total_coefficient, moyenne, nombre_ue, nombre_ue_validees, nombre_ue_a_valider, nombre_note_eliminatoire, decision
    from resultats_avant_repechage as r
    join au on r.id_au = au.id_au
    join parcours as p on r.id_parcours = p.id_parcours
    join niveau as n on r.id_niveau = n.id_niveau
    join unite_enseignement as ue on r.id_unite_enseignement = ue.id_unite_enseignement
    join element_constitutif as ec on r.id_element_constitutif = ec.id_element_constitutif
    left join etudiants as e on r.id_etudiants = e.id_etudiants;


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



-- ******** ---

    -- GESTION DES NOTES
    -- RESULTATS SUR L'A.U.
    -- PRISE EN COMPTE DES NOTES DE REPECHAGE

-- ******** ---
    --18-11-24 18:32
    --  note moyenne de chaque ue repêchée
    create or replace view v_note_moyenne_ue_rep as
            select id_au, id_parcours, id_niveau, coefficient, id_unite_enseignement, id_examen_par_au, id_session_examen, im, id_etudiants,avg(note) as note, date_annulation, statut, a_passe_examen
            from v_note
            WHERE type_session = 'repe'
            group by id_au, id_parcours, id_niveau, coefficient, id_unite_enseignement, id_examen_par_au, id_session_examen, im, id_etudiants, date_annulation, statut, a_passe_examen;

    -- assemblage des ue REPÊCHEES avec leurs ec respectifs
    create or replace view v_note_ue_avec_ec_rep as
        select n.id_au, n.id_parcours, n.id_niveau, n.id_examen_par_au, n.id_session_examen, n.nom_session_examen, n.type_session, n.date_annulation, n.coefficient, n.id_unite_enseignement, n.id_ue_ec, n.id_element_constitutif, n.im, n.id_etudiants, n.note as note_ec, v.note as note_ue, n.statut, n.a_passe_examen
        from v_note_moyenne_ue_rep as v
        join v_note as n on (n.id_au = v.id_au and n.id_parcours = v.id_parcours and n.id_niveau = v.id_niveau and n.id_unite_enseignement = v.id_unite_enseignement and n.id_examen_par_au = v.id_examen_par_au and n.id_etudiants = v.id_etudiants) or (n.id_au = v.id_au and n.id_parcours = v.id_parcours and n.id_niveau = v.id_niveau and n.id_unite_enseignement = v.id_unite_enseignement and n.id_examen_par_au = v.id_examen_par_au and n.id_etudiants is null and v.id_etudiants is null);


    --  assemblage des notes des évaluations avec les notes de repêchage PAR EC, on retiendra la note supérieure entre évaluation et repechage par ue par étudiant
    create or replace view v_assemblage_eval_repe as
        select eval.id_au, eval.id_parcours, eval.id_niveau, eval.id_examen_par_au, eval.id_session_examen, eval.nom_session_examen,
        CASE
            WHEN rep.note_ue >= eval.note_ue THEN rep.type_session
            WHEN eval.note_ue > rep.note_ue THEN eval.type_session
        END as type_session_retenue,
        eval.date_annulation_inscription, eval.coefficient, eval.id_unite_enseignement, eval.id_element_constitutif, eval.im, eval.id_etudiants,
        GREATEST(rep.note_ue, eval.note_ue) as note_ue,
        CASE
            WHEN rep.note_ue >= eval.note_ue THEN rep.note_ec
            WHEN eval.note_ue > rep.note_ue THEN eval.note_ec
        END as note_ec,
        rep.statut, rep.a_passe_examen
        from v_note_ue_avec_ec_rep as rep
        join resultats_avant_repechage as eval on (rep.id_au = eval.id_au and rep.id_parcours = eval.id_parcours and rep.id_niveau = eval.id_niveau  and rep.id_unite_enseignement = eval.id_unite_enseignement and  rep.id_element_constitutif = eval.id_element_constitutif and rep.id_etudiants = eval.id_etudiants) or (rep.id_au = eval.id_au and rep.id_parcours = eval.id_parcours and rep.id_niveau = eval.id_niveau  and rep.id_unite_enseignement = eval.id_unite_enseignement and  rep.id_element_constitutif = eval.id_element_constitutif and rep.id_etudiants is null and eval.id_etudiants is null);




    -- elimination des elements constitutifs
    create or replace view v_note_ue_rep as
        select distinct on(id_au, id_parcours, id_niveau, id_examen_par_au, id_unite_enseignement, id_etudiants) id_au, id_parcours, id_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session_retenue, date_annulation_inscription, coefficient, id_unite_enseignement, im, id_etudiants, note_ue, statut, a_passe_examen
        from v_assemblage_eval_repe
        order by id_au, id_parcours, id_niveau, id_examen_par_au, id_unite_enseignement, id_etudiants, id_element_constitutif;


    -- RÉÉVALUATION DES CONDITIONS DE PASSAGE
    -- vue des totaux des notes
    create or replace view v_total_note_rep as
        select id_au, id_parcours, id_niveau, date_annulation_inscription, im, id_etudiants, sum(note_ue * coefficient) as total, sum(coefficient) as total_coefficient, statut, a_passe_examen
        from v_note_ue_rep
        group by id_au, id_parcours, id_niveau, date_annulation_inscription, im, id_etudiants, statut, a_passe_examen;

    -- vue des moyennes
    create or replace view v_moyenne_rep as
        select id_au, id_parcours, id_niveau, date_annulation_inscription, im, id_etudiants,total, total_coefficient, (total/total_coefficient) as moyenne, statut, a_passe_examen
        from v_total_note_rep;

    -- validation des UE
    create or replace view v_validation_ue_rep as
        select id_au, id_parcours, id_niveau, id_examen_par_au, id_session_examen, nom_session_examen, type_session_retenue, date_annulation_inscription, coefficient, id_unite_enseignement, im, id_etudiants, note_ue, statut, a_passe_examen,
            case
                when note_ue >= (select note_validation_ue as moyenne from note_validation_ue order by id_note_validation_ue desc limit 1) then 'V'
                when note_ue > (select note_elim from note_eliminatoire order by id_note_eliminatoire desc limit 1) and note_ue < (select note_validation_ue from note_validation_ue order by id_note_validation_ue desc limit 1) then 'N'
                else 'E'
            end AS valide
        from v_note_ue_rep;

    -- COMPTAGE DES UE VALIDEES
    -- nombre des ue_validees
    create or replace view v_nombre_ue_validees_rep as
        select id_au, id_parcours, id_niveau, im, id_etudiants, count(valide) as nombre_ue_validees
        from v_validation_ue_rep
        where valide = 'V'
        group by id_au, id_parcours, id_niveau, im, id_etudiants, statut;

    -- cas des parcours et niveaux sans inscrits
    create or replace view v_nombre_ue_validees_sans_inscrits_rep as
        select distinct on(id_au, id_parcours, id_niveau) id_au, id_parcours, id_niveau, im, id_etudiants, 0 as nombre_ue_validees
        from v_validation_ue_rep
        where id_etudiants is null
        order by id_au, id_parcours, id_niveau, id_examen_par_au asc ,id_unite_enseignement asc;

    -- assemblage des deux nombres d'UE validées
    create or replace view v_nombre_ue_validees_complet_rep as
        select * from v_nombre_ue_validees_rep
        union all
        select * from  v_nombre_ue_validees_sans_inscrits_rep;



    -- nombre d'UE avec notes eliminatoires
    create or replace view v_nombre_note_eliminatoire_rep as
        select id_au, id_parcours, id_niveau, im, id_etudiants, count(valide) as nombre_note_eliminatoire
        from v_validation_ue_rep
        where valide = 'E'
        group by id_au, id_parcours, id_niveau, im, id_etudiants;

    --assemblage des conditions de passage
    create or replace view v_conditions_passage as
        select m.id_au, m.id_parcours, m.id_niveau, m.im, m.id_etudiants, m.date_annulation_inscription, m.statut, m.a_passe_examen, m.total, m.total_coefficient, m.moyenne,  av.nombre_ue, av.nombre_ue_a_valider, nv.nombre_ue_validees, ne.nombre_note_eliminatoire
        from v_moyenne_rep as m
        join v_nombre_ue_validees_complet_rep as nv on (nv.id_au = m.id_au and nv.id_parcours = m.id_parcours and nv.id_niveau = m.id_niveau and nv.id_etudiants = m.id_etudiants) or (nv.id_au = m.id_au and nv.id_parcours = m.id_parcours and nv.id_niveau = m.id_niveau and nv.id_etudiants is null and m.id_etudiants is null)
        join v_nombre_ue_a_valider as av on av.id_au = m.id_au and av.id_parcours = m.id_parcours and av.id_niveau = m.id_niveau
        join v_nombre_note_eliminatoire_rep as ne on (ne.id_au = m.id_au and ne.id_parcours = m.id_parcours and ne.id_niveau = m.id_niveau and ne.id_etudiants = m.id_etudiants) or (ne.id_au = m.id_au and ne.id_parcours = m.id_parcours and ne.id_niveau = m.id_niveau and ne.id_etudiants is null and m.id_etudiants is null);


    -- CAS DES ETUDIANTS NE REMPLISSANT PAS LES CONDITIONS DE PASSAGE

    create or replace function statuer(a_id_etudiant bigint, a_id_niveau bigint)
        RETURNS VARCHAR AS
        $$
        DECLARE
            niveau_v RECORD;
            nb_triplements INTEGER;
            nb_redoublements INTEGER;
            dernier_redoublement RECORD;
        BEGIN
            SELECT *
            INTO niveau_v
            FROM niveau
            WHERE id_niveau = a_id_niveau;

            -- récuperer les triplements
            CREATE TEMP TABLE triplements AS
                SELECT DISTINCT ON(id_au, id_etudiants) id_au,  id_etudiants, statut_au_suivante
                FROM resultats_definitifs
                WHERE id_etudiants = a_id_etudiant
                AND cycle = niveau_v.cycle
                AND statut_au_suivante = 'triplant'
                ORDER BY id_au asc, id_etudiants asc , id_ue asc;

            SELECT COUNT(*) INTO nb_triplements FROM triplements;
            IF nb_triplements > 0
                THEN
                    DROP TABLE triplements;
                    RETURN 'exclu'::VARCHAR;
            END IF;
            DROP TABLE triplements;

            -- cas des redoublements
            CREATE TEMP TABLE redoublements AS
                SELECT DISTINCT ON(id_au, id_etudiants) id_au, id_etudiants, id_niveau, rang, statut_au_suivante
                FROM resultats_definitifs
                WHERE id_etudiants = a_id_etudiant
                AND cycle = niveau_v.cycle
                AND statut_au_suivante = 'redoublant'
                ORDER BY id_au asc, id_etudiants asc, id_ue asc;

            SELECT COUNT(*) INTO nb_redoublements FROM redoublements;
            IF nb_redoublements >= 2
                THEN
                    DROP TABLE redoublements;
                    return 'exclu'::VARCHAR;
            ELSEIF nb_redoublements = 1
                THEN
                    SELECT *
                    INTO dernier_redoublement
                    FROM redoublements;

                    -- cas des niveaux consécutifs
                    IF ABS(niveau_v.rang - dernier_redoublement.rang) = 1
                        THEN
                            DROP TABLE redoublements;
                            RETURN 'exclu'::VARCHAR;

                    -- cas de niveaux non consécutifs
                    ELSEIF ABS(niveau_v.rang - dernier_redoublement.rang) > 1
                        THEN
                            DROP TABLE redoublements;
                            RETURN 'redoublant'::VARCHAR;

                    -- cas d'un même niveaus
                    ELSEIF ABS(niveau_v.rang - dernier_redoublement.rang) = 0
                        THEN
                            DROP TABLE redoublements;
                            RETURN 'triplant'::VARCHAR;
                    END IF;
            ELSEIF nb_redoublements = 0
                THEN
                    DROP TABLE redoublements;
                    RETURN 'redoublant'::VARCHAR;
            END IF;
    END;
    $$ LANGUAGE plpgsql;

        -- créationd de la fonction statuer pour décider de leur statut à l'année suivante


    -- decision avant la deliberation

    create or replace view v_statut_avant_deliberation as
        select id_au, id_parcours, id_niveau, im, id_etudiants, date_annulation_inscription, statut, a_passe_examen, total, total_coefficient, (select moyenne_admission from moyenne_admission order by id_moyenne_admission desc limit 1) as moyenne_passage, moyenne,  nombre_ue, nombre_ue_a_valider, nombre_ue_validees, nombre_note_eliminatoire,
        CASE
            WHEN date_annulation_inscription is not null AND statut = 'passant' AND a_passe_examen = FALSE
                THEN 'passant'::VARCHAR
            WHEN date_annulation_inscription is not null AND statut = 'passant' AND a_passe_examen = TRUE
                THEN 'redoublant'::VARCHAR
            WHEN
                moyenne >= (select moyenne_admission from moyenne_admission order by id_moyenne_admission desc limit 1)
                AND nombre_ue_validees >= nombre_ue_a_valider
                AND nombre_note_eliminatoire = 0
                THEN  'passant'::varchar
            ELSE statuer(id_etudiants, id_niveau)
        END AS statut_au_suivante
        from v_conditions_passage;



    -- fonction pour récupérer le niveau à l'année suivante
    create or replace function get_niveau_suivant(a_statut VARCHAR, a_id_niveau BIGINT, date_annulation DATE)
        RETURNS BIGINT AS
        $$
        DECLARE
            niveau_v RECORD;
            niveau_suivant RECORD;
        BEGIN
            SELECT *
            INTO niveau_v
            FROM niveau
            WHERE id_niveau = a_id_niveau;

            IF a_statut = 'exclu'
                THEN RETURN NULL;
            ELSEIF date_annulation IS NOT NULL
                THEN RETURN a_id_niveau;
            ELSEIF a_statut = 'redoublant' or a_statut = 'triplant'
                THEN RETURN a_id_niveau;
            ELSEIF a_statut = 'passant'
                THEN
                    SELECT *
                    INTO niveau_suivant
                    FROM niveau
                    WHERE rang = niveau_v.rang + 1;

                    IF niveau_suivant IS NOT NULL
                        THEN RETURN niveau_suivant.id_niveau;
                    ELSE
                        RETURN NULL;
                    END IF;
            END IF;

        END;
    $$ LANGUAGE plpgsql;



    -- niveau pour l'année universtaire suivante
    create or replace view v_niveau_suivant_avant_deliberation as
        select id_au, id_parcours, id_niveau, im, id_etudiants, date_annulation_inscription, statut, a_passe_examen, total, total_coefficient, moyenne_passage, moyenne,  nombre_ue, nombre_ue_a_valider, nombre_ue_validees, nombre_note_eliminatoire,statut_au_suivante, get_niveau_suivant(statut_au_suivante, id_niveau, date_annulation_inscription) as niveau_suivant

        from v_statut_avant_deliberation;


    -- assemblage avec les ue et ec

    CREATE OR REPLACE VIEW v_calcul_resultats_avant_deliberation AS
         SELECT
             eval.id_au,
             eval.id_parcours,
             eval.id_niveau,
             eval.id_etudiants,

             eval.id_examen_par_au,
             eval.id_session_examen,
             eval.nom_session_examen,
             eval.type_session_retenue,
             eval.coefficient,
             eval.id_unite_enseignement,
             eval.id_element_constitutif,
             eval.im,
             eval.note_ue,
             eval.note_ec,
             val.valide,
             eval.statut,
             eval.a_passe_examen,

             niv.date_annulation_inscription,
             niv.total,
             niv.total_coefficient,
             niv.moyenne_passage,
             niv.moyenne,
             niv.nombre_ue,
             niv.nombre_ue_a_valider,
             niv.nombre_ue_validees,
             niv.nombre_note_eliminatoire,
             niv.statut_au_suivante,
             niv.niveau_suivant

         FROM
             v_assemblage_eval_repe AS eval
         JOIN
             v_niveau_suivant_avant_deliberation AS niv
         ON
             eval.id_au = niv.id_au AND
             eval.id_parcours = niv.id_parcours AND
             eval.id_niveau = niv.id_niveau AND
             (eval.id_etudiants = niv.id_etudiants OR (eval.id_etudiants IS NULL AND niv.id_etudiants IS NULL))

        JOIN
            v_validation_ue_rep AS val
        ON
            eval.id_au = val.id_au AND
            eval.id_parcours = val.id_parcours AND
            eval.id_niveau = val.id_niveau AND
            (eval.id_etudiants = val.id_etudiants OR (val.id_etudiants IS NULL AND val.id_etudiants IS NULL)) AND
            eval.id_unite_enseignement = val.id_unite_enseignement;





    -- CREATION DE LA TABLE RESULTATS AVANT DELIBERATION

    -- résultats complets avant délibération (VM RAFRAICHIE A L GENERATION DES RESULTATS AVANT DELIBERATION )
    CREATE MATERIALIZED VIEW v_resultats_avant_deliberation AS
        SELECT
            ra.id_resultat_avant_deliberation,
            ra.id_au,
            ra.id_parcours,
            ra.id_niveau,
            ra.id_etudiants,
            ra.id_examen_par_au,
            ra.id_session_examen,
            ra.nom_session_examen,
            ra.type_session_retenue,
            ra.coefficient,
            ra.id_unite_enseignement,
            ra.id_element_constitutif,
            ra.im,
            ra.note_ue,
            ra.note_ec,
            ra.valide,
            ra.statut,
            ra.a_passe_examen,
            ra.date_annulation_inscription,
            ra.total,
            ra.total_coefficient,
            ra.moyenne_passage,
            ra.moyenne,
            ra.nombre_ue,
            ra.nombre_ue_a_valider,
            ra.nombre_ue_validees,
            ra.nombre_note_eliminatoire,
            ra.statut_au_suivante,
            ra.id_niveau_suivant,

            au.intitule,

            p.nom_parcours,


            n.nom_niveau,
            n.rang,
            n.nom_niveau_long,
            n.cycle,

            e.nom,
            e.prenoms,
            e.date_naissance,
            e.lieu_naissance,

            ue.nom_unite_enseignement,

            ec.nom_element_constitutif,

            ns.nom_niveau AS nom_niveau_suivant,
            ns.rang AS rang_suivant,
            ns.cycle as cycle_suivant
        FROM
            resultats_avant_deliberation ra
        JOIN
            au ON ra.id_au = au.id_au
        JOIN
            parcours p ON ra.id_parcours = p.id_parcours
        JOIN
            niveau n ON ra.id_niveau = n.id_niveau
        JOIN
            etudiants e ON ra.id_etudiants = e.id_etudiants
        JOIN
            unite_enseignement ue ON ra.id_unite_enseignement = ue.id_unite_enseignement
        JOIN
            element_constitutif ec ON ra.id_element_constitutif = ec.id_element_constitutif
        LEFT JOIN
            niveau ns ON ra.id_niveau_suivant = ns.id_niveau;



    -- CREATION DE LA TABLE RESULTATS DEFINITIFS

-- ******** ---

    -- GESTION DES NOTES
    -- RESULTATS SUR L'A.U.
    -- DELIBERATION

-- ******** ---
    --  étudiants non admis aux examens
    CREATE OR REPLACE VIEW v_non_admis as
        SELECT *
        FROM resultats_definitifs
        WHERE statut_au_suivante != 'passant' AND id_etudiants IS NOT NULL AND date_annulation_inscription is NULL;

    -- historique de redoublement et de triplement
    -- historique de redoublement et de triplement
    CREATE OR REPLACE VIEW v_historique_redoublement_triplement AS
        SELECT DISTINCT ON(id_etudiants, id_au) id_au, intitule, id_parcours, nom_parcours, id_niveau, nom_niveau, id_etudiants, im, nom_etudiant, prenoms, statut_au_suivante
        FROM resultats_definitifs
        WHERE (statut_au_suivante = 'redoublant' or statut_au_suivante = 'triplant')  AND date_annulation_inscription is NULL
        ORDER BY id_etudiants, id_au, id_ue;







