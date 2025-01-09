--
-- PostgreSQL database dump
--

-- Dumped from database version 14.15 (Ubuntu 14.15-0ubuntu0.22.04.1)
-- Dumped by pg_dump version 14.15 (Ubuntu 14.15-0ubuntu0.22.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: creer_v_liste_repechage_affichage(bigint, bigint, bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.creer_v_liste_repechage_affichage(a_id_au bigint, a_id_parcours bigint, a_id_niveau bigint) RETURNS void
    LANGUAGE plpgsql
    AS $$
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
                            ', Max(CASE WHEN nom_unite_enseignement = ''' || nom_ue.nom_unite_enseignement || ''' THEN valide END) AS "Résultats ' || nom_ue.nom_unite_enseignement || '"';
                    END LOOP;

                    requete := requete || colonnes || ' FROM  v_liste_repechage WHERE id_au = ' || a_id_au || ' and id_parcours = ' || a_id_parcours || ' and id_niveau = ' || a_id_niveau ||  ' GROUP BY im, nom, prenoms;' ;

                    EXECUTE 'DROP MATERIALIZED VIEW  if exists v_liste_repechage_affichage;' ;
                    RAISE NOTICE '%',requete;
                    EXECUTE requete;


                END;
                $$;


ALTER FUNCTION public.creer_v_liste_repechage_affichage(a_id_au bigint, a_id_parcours bigint, a_id_niveau bigint) OWNER TO postgres;

--
-- Name: creer_v_resultats_eval(bigint, bigint, bigint, bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.creer_v_resultats_eval(a_id_au bigint, a_id_parcours bigint, a_id_niveau bigint, a_id_examen_par_au bigint) RETURNS void
    LANGUAGE plpgsql
    AS $$
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
                            ', Max(CASE WHEN nom_unite_enseignement = ''' || nom_ue.nom_unite_enseignement || ''' THEN valide END) AS "Résultats ' || nom_ue.nom_unite_enseignement || '"';
                    END LOOP;

                    requete := requete || colonnes || ' FROM  v_note_eval_ue_complet WHERE id_au = ' || a_id_au || ' and id_parcours = ' || a_id_parcours || ' and id_niveau = ' || a_id_niveau || ' and id_examen_par_au = ' || a_id_examen_par_au || ' GROUP BY im, nom, prenoms;' ;

                    EXECUTE 'DROP view if exists v_resultats_eval;' ;
                    RAISE NOTICE '%',requete;
                    EXECUTE requete;


                END;
                $$;


ALTER FUNCTION public.creer_v_resultats_eval(a_id_au bigint, a_id_parcours bigint, a_id_niveau bigint, a_id_examen_par_au bigint) OWNER TO postgres;

--
-- Name: f_association_etu_ec(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.f_association_etu_ec(p_id_examen_par_au bigint) RETURNS TABLE(id_au bigint, id_parcours bigint, id_niveau bigint, coefficient double precision, id_unite_enseignement bigint, id_ue_ec bigint, id_examen_par_au bigint, id_session_examen bigint, nom_session_examen character varying, type_session character varying, id_etudiants bigint, im character varying, date_annulation date)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                return query select i.id_au, i.id_parcours, i.id_niveau, ue_ec.coefficient, ue_ec.id_unite_enseignement, ue_ec.id_ue_ec, ue_ec.id_examen_par_au, ue_ec.id_session_examen, ue_ec.nom_session_examen, ue_ec.type_session,i.id_etudiants, i.im, i.date_annulation
                                from ue_ec_eval_filtre(p_id_examen_par_au) as ue_ec
                                left join v_inscrits2 as i on i.id_parcours = ue_ec.id_parcours and i.id_niveau =  ue_ec.id_niveau and i.id_au = ue_ec.id_au;

            end;
            $$;


ALTER FUNCTION public.f_association_etu_ec(p_id_examen_par_au bigint) OWNER TO postgres;

--
-- Name: f_note(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.f_note(p_id_examen_par_au bigint) RETURNS TABLE(id_au bigint, id_parcours bigint, id_niveau bigint, id_unite_enseignement bigint, id_ue_ec bigint, id_examen_par_au bigint, id_session_examen bigint, nom_session_examen character varying, type_session character varying, im character varying, id_etudiants bigint, note double precision)
    LANGUAGE plpgsql
    AS $$
            begin
                return query select a.id_au, a.id_parcours, a.id_niveau, a.id_unite_enseignement, a.id_ue_ec, a.id_examen_par_au, a.id_session_examen, a.nom_session_examen, a.type_session, a.im, a.id_etudiants,coalesce(n.note, 0) as note
                                from v_correspondance_note_matricule as n
                                right join f_association_etu_ec(p_id_examen_par_au) as a on a.id_ue_ec = n.id_ue_ec and a.im = n.matricule;
            end;
            $$;


ALTER FUNCTION public.f_note(p_id_examen_par_au bigint) OWNER TO postgres;

--
-- Name: get_niveau_suivant(character varying, bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_niveau_suivant(a_statut character varying, a_id_niveau bigint) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
                 DECLARE
                     niveau_v RECORD;
                     niveau_suivant RECORD;
                 BEGIN
                     SELECT *
                     INTO niveau_v
                     FROM niveau
                     WHERE id_niveau = a_id_niveau;

                     IF a_statut = 'exclus'
                        THEN RETURN NULL;
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
            $$;


ALTER FUNCTION public.get_niveau_suivant(a_statut character varying, a_id_niveau bigint) OWNER TO postgres;

--
-- Name: get_niveau_suivant(character varying, bigint, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_niveau_suivant(a_statut character varying, a_id_niveau bigint, date_annulation date) RETURNS bigint
    LANGUAGE plpgsql
    AS $$
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
    $$;


ALTER FUNCTION public.get_niveau_suivant(a_statut character varying, a_id_niveau bigint, date_annulation date) OWNER TO postgres;

--
-- Name: statuer(bigint, bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.statuer(a_id_etudiant bigint, a_id_niveau bigint) RETURNS character varying
    LANGUAGE plpgsql
    AS $$
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
    $$;


ALTER FUNCTION public.statuer(a_id_etudiant bigint, a_id_niveau bigint) OWNER TO postgres;

--
-- Name: ue_ec_eval_filtre(bigint); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.ue_ec_eval_filtre(p_id_examen_par_au bigint) RETURNS TABLE(id_ue_ec bigint, coefficient double precision, id_examen_par_au bigint, id_parcours bigint, id_niveau bigint, id_unite_enseignement bigint, id_element_constitutif bigint, id_au bigint, id_session_examen bigint, nom_session_examen character varying, type_session character varying)
    LANGUAGE plpgsql
    AS $$
            BEGIN
                RETURN QUERY SELECT v.id_ue_ec, v.coefficient , v.id_examen_par_au, v.id_parcours, v.id_niveau, v.id_unite_enseignement, v.id_element_constitutif, v.id_au, v.id_session_examen, v.nom_session_examen, v.type_session FROM v_ue_ec_eval as v
                                WHERE v.id_examen_par_au = p_id_examen_par_au;
            END;
            $$;


ALTER FUNCTION public.ue_ec_eval_filtre(p_id_examen_par_au bigint) OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: au; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.au (
    id_au bigint NOT NULL,
    intitule character varying(255) NOT NULL,
    ouverture date NOT NULL,
    cloture character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.au OWNER TO postgres;

--
-- Name: au_id_au_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.au_id_au_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.au_id_au_seq OWNER TO postgres;

--
-- Name: au_id_au_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.au_id_au_seq OWNED BY public.au.id_au;


--
-- Name: autres_etablissements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.autres_etablissements (
    id_autre_etablissement bigint NOT NULL,
    nom_autre_etablissement character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.autres_etablissements OWNER TO postgres;

--
-- Name: autres_etablissements_id_autre_etablissement_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.autres_etablissements_id_autre_etablissement_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.autres_etablissements_id_autre_etablissement_seq OWNER TO postgres;

--
-- Name: autres_etablissements_id_autre_etablissement_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.autres_etablissements_id_autre_etablissement_seq OWNED BY public.autres_etablissements.id_autre_etablissement;


--
-- Name: autres_inscriptions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.autres_inscriptions (
    id_au bigint NOT NULL,
    id_etudiants bigint NOT NULL,
    etablissement character varying(255) NOT NULL,
    niveau character varying(50) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.autres_inscriptions OWNER TO postgres;

--
-- Name: barcode_matricule; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.barcode_matricule (
    id_ue_ec bigint NOT NULL,
    numero integer NOT NULL,
    matricule character varying(255) NOT NULL,
    verifie boolean,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.barcode_matricule OWNER TO postgres;

--
-- Name: barcode_note; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.barcode_note (
    id_ue_ec bigint NOT NULL,
    numero integer NOT NULL,
    note double precision NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    verifie boolean
);


ALTER TABLE public.barcode_note OWNER TO postgres;

--
-- Name: correspondance_mention_ent; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.correspondance_mention_ent (
    id_correspondance_mention_ent bigint NOT NULL,
    id_mention bigint NOT NULL,
    id_mention_ent bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.correspondance_mention_ent OWNER TO postgres;

--
-- Name: correspondance_mention_ent_id_correspondance_mention_ent_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.correspondance_mention_ent_id_correspondance_mention_ent_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.correspondance_mention_ent_id_correspondance_mention_ent_seq OWNER TO postgres;

--
-- Name: correspondance_mention_ent_id_correspondance_mention_ent_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.correspondance_mention_ent_id_correspondance_mention_ent_seq OWNED BY public.correspondance_mention_ent.id_correspondance_mention_ent;


--
-- Name: correspondance_parcours_ent; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.correspondance_parcours_ent (
    id_correspondance_parcours_ent bigint NOT NULL,
    id_parcours bigint NOT NULL,
    id_parcours_ent bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.correspondance_parcours_ent OWNER TO postgres;

--
-- Name: correspondance_parcours_ent_id_correspondance_parcours_ent_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.correspondance_parcours_ent_id_correspondance_parcours_ent_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.correspondance_parcours_ent_id_correspondance_parcours_ent_seq OWNER TO postgres;

--
-- Name: correspondance_parcours_ent_id_correspondance_parcours_ent_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.correspondance_parcours_ent_id_correspondance_parcours_ent_seq OWNED BY public.correspondance_parcours_ent.id_correspondance_parcours_ent;


--
-- Name: cte_codes_barres_en_plus; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cte_codes_barres_en_plus (
    id_cte_codes_barres_en_plus bigint NOT NULL,
    cte_codes_barres_en_plus integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.cte_codes_barres_en_plus OWNER TO postgres;

--
-- Name: cte_codes_barres_en_plus_id_cte_codes_barres_en_plus_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.cte_codes_barres_en_plus_id_cte_codes_barres_en_plus_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.cte_codes_barres_en_plus_id_cte_codes_barres_en_plus_seq OWNER TO postgres;

--
-- Name: cte_codes_barres_en_plus_id_cte_codes_barres_en_plus_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.cte_codes_barres_en_plus_id_cte_codes_barres_en_plus_seq OWNED BY public.cte_codes_barres_en_plus.id_cte_codes_barres_en_plus;


--
-- Name: element_constitutif; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.element_constitutif (
    id_element_constitutif bigint NOT NULL,
    nom_element_constitutif character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.element_constitutif OWNER TO postgres;

--
-- Name: element_constitutif_id_element_constitutif_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.element_constitutif_id_element_constitutif_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.element_constitutif_id_element_constitutif_seq OWNER TO postgres;

--
-- Name: element_constitutif_id_element_constitutif_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.element_constitutif_id_element_constitutif_seq OWNED BY public.element_constitutif.id_element_constitutif;


--
-- Name: etudiants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.etudiants (
    id_etudiants bigint NOT NULL,
    im character varying(50) NOT NULL,
    nom character varying(255) NOT NULL,
    prenoms character varying(255) NOT NULL,
    sexe character varying(255) NOT NULL,
    date_premiere_inscription date NOT NULL,
    date_naissance date NOT NULL,
    lieu_naissance character varying(255) NOT NULL,
    num_piece_identite character varying(100),
    date_delivrance date,
    lieu_delivrance character varying(255),
    adresse character varying(255) NOT NULL,
    telephone character varying(20) NOT NULL,
    pere character varying(255) NOT NULL,
    profession_pere character varying(255) NOT NULL,
    tel_pere character varying(20) NOT NULL,
    adresse_pere character varying(255) NOT NULL,
    mere character varying(255) NOT NULL,
    profession_mere character varying(255) NOT NULL,
    tel_mere character varying(20) NOT NULL,
    adresse_mere character varying(255) NOT NULL,
    est_officier boolean NOT NULL,
    id_parcours bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    id_agent bigint NOT NULL,
    annee_bacc integer NOT NULL,
    id_serie bigint NOT NULL,
    id_province bigint NOT NULL,
    id_nationalite bigint NOT NULL,
    type_piece_identite character varying(255),
    email character varying(255),
    id_etablissement_transfert bigint,
    id_au_transfert bigint,
    id_niveau_transfert bigint,
    photo character varying(255) NOT NULL,
    CONSTRAINT etudiants_sexe_check CHECK (((sexe)::text = ANY ((ARRAY['m'::character varying, 'f'::character varying])::text[]))),
    CONSTRAINT etudiants_type_piece_identite_check CHECK (((type_piece_identite)::text = ANY ((ARRAY['cin'::character varying, 'pass'::character varying])::text[])))
);


ALTER TABLE public.etudiants OWNER TO postgres;

--
-- Name: etudiants_id_etudiants_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.etudiants_id_etudiants_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.etudiants_id_etudiants_seq OWNER TO postgres;

--
-- Name: etudiants_id_etudiants_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.etudiants_id_etudiants_seq OWNED BY public.etudiants.id_etudiants;


--
-- Name: examen_par_au; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.examen_par_au (
    id_examen_par_au bigint NOT NULL,
    id_session_examen bigint NOT NULL,
    id_au bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.examen_par_au OWNER TO postgres;

--
-- Name: examen_par_au_id_examen_par_au_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.examen_par_au_id_examen_par_au_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.examen_par_au_id_examen_par_au_seq OWNER TO postgres;

--
-- Name: examen_par_au_id_examen_par_au_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.examen_par_au_id_examen_par_au_seq OWNED BY public.examen_par_au.id_examen_par_au;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: inscription; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.inscription (
    id_inscription bigint NOT NULL,
    date_inscription date NOT NULL,
    date_annulation date,
    id_agent_inscription bigint NOT NULL,
    id_agent_annulation bigint,
    id_etudiant bigint NOT NULL,
    id_niveau bigint NOT NULL,
    id_au bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    date_certificat_scol date,
    statut character varying(255) NOT NULL,
    a_passe_examen boolean,
    CONSTRAINT inscription_statut_check CHECK (((statut)::text = ANY ((ARRAY['passant'::character varying, 'redoublant'::character varying])::text[])))
);


ALTER TABLE public.inscription OWNER TO postgres;

--
-- Name: inscription_id_inscription_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.inscription_id_inscription_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.inscription_id_inscription_seq OWNER TO postgres;

--
-- Name: inscription_id_inscription_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.inscription_id_inscription_seq OWNED BY public.inscription.id_inscription;


--
-- Name: mention; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mention (
    id_mention bigint NOT NULL,
    nom_mention character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.mention OWNER TO postgres;

--
-- Name: mention_ent; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.mention_ent (
    id_mention_ent bigint NOT NULL,
    nom_mention_ent character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.mention_ent OWNER TO postgres;

--
-- Name: mention_ent_id_mention_ent_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.mention_ent_id_mention_ent_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mention_ent_id_mention_ent_seq OWNER TO postgres;

--
-- Name: mention_ent_id_mention_ent_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mention_ent_id_mention_ent_seq OWNED BY public.mention_ent.id_mention_ent;


--
-- Name: mention_id_mention_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.mention_id_mention_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.mention_id_mention_seq OWNER TO postgres;

--
-- Name: mention_id_mention_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.mention_id_mention_seq OWNED BY public.mention.id_mention;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: moyenne_admission; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.moyenne_admission (
    id_moyenne_admission bigint NOT NULL,
    moyenne_admission double precision NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.moyenne_admission OWNER TO postgres;

--
-- Name: moyenne_admission_id_moyenne_admission_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.moyenne_admission_id_moyenne_admission_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.moyenne_admission_id_moyenne_admission_seq OWNER TO postgres;

--
-- Name: moyenne_admission_id_moyenne_admission_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.moyenne_admission_id_moyenne_admission_seq OWNED BY public.moyenne_admission.id_moyenne_admission;


--
-- Name: nationalites; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.nationalites (
    id_nationalites bigint NOT NULL,
    nom_nationalite character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.nationalites OWNER TO postgres;

--
-- Name: nationalites_id_nationalites_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.nationalites_id_nationalites_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.nationalites_id_nationalites_seq OWNER TO postgres;

--
-- Name: nationalites_id_nationalites_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.nationalites_id_nationalites_seq OWNED BY public.nationalites.id_nationalites;


--
-- Name: niveau; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.niveau (
    id_niveau bigint NOT NULL,
    nom_niveau character varying(255) NOT NULL,
    rang integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    nom_niveau_long character varying(255),
    cycle integer NOT NULL
);


ALTER TABLE public.niveau OWNER TO postgres;

--
-- Name: niveau_id_niveau_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.niveau_id_niveau_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.niveau_id_niveau_seq OWNER TO postgres;

--
-- Name: niveau_id_niveau_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.niveau_id_niveau_seq OWNED BY public.niveau.id_niveau;


--
-- Name: note_eliminatoire; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.note_eliminatoire (
    id_note_eliminatoire bigint NOT NULL,
    note_elim numeric(8,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.note_eliminatoire OWNER TO postgres;

--
-- Name: note_eliminatoire_id_note_eliminatoire_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.note_eliminatoire_id_note_eliminatoire_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.note_eliminatoire_id_note_eliminatoire_seq OWNER TO postgres;

--
-- Name: note_eliminatoire_id_note_eliminatoire_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.note_eliminatoire_id_note_eliminatoire_seq OWNED BY public.note_eliminatoire.id_note_eliminatoire;


--
-- Name: note_eval; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.note_eval (
    id_note_eval bigint NOT NULL,
    id_au bigint NOT NULL,
    id_parcours bigint NOT NULL,
    id_niveau bigint NOT NULL,
    id_examen_par_au bigint NOT NULL,
    id_session_examen bigint NOT NULL,
    nom_session_examen character varying(255) NOT NULL,
    type_session character varying(255) NOT NULL,
    date_annulation_inscription date,
    coefficient double precision NOT NULL,
    id_unite_enseignement bigint NOT NULL,
    id_ue_ec bigint NOT NULL,
    id_element_constitutif bigint NOT NULL,
    im character varying(255),
    id_etudiants bigint,
    note_ec double precision NOT NULL,
    note_ue double precision NOT NULL,
    valide character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT note_eval_valide_check CHECK (((valide)::text = ANY ((ARRAY['E'::character varying, 'N'::character varying, 'V'::character varying])::text[])))
);


ALTER TABLE public.note_eval OWNER TO postgres;

--
-- Name: note_eval_id_note_eval_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.note_eval_id_note_eval_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.note_eval_id_note_eval_seq OWNER TO postgres;

--
-- Name: note_eval_id_note_eval_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.note_eval_id_note_eval_seq OWNED BY public.note_eval.id_note_eval;


--
-- Name: note_max; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.note_max (
    id_note_max bigint NOT NULL,
    note_max double precision NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.note_max OWNER TO postgres;

--
-- Name: note_max_id_note_max_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.note_max_id_note_max_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.note_max_id_note_max_seq OWNER TO postgres;

--
-- Name: note_max_id_note_max_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.note_max_id_note_max_seq OWNED BY public.note_max.id_note_max;


--
-- Name: note_validation_ue; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.note_validation_ue (
    id_note_validation_ue bigint NOT NULL,
    note_validation_ue double precision NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.note_validation_ue OWNER TO postgres;

--
-- Name: note_validation_ue_id_note_validation_ue_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.note_validation_ue_id_note_validation_ue_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.note_validation_ue_id_note_validation_ue_seq OWNER TO postgres;

--
-- Name: note_validation_ue_id_note_validation_ue_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.note_validation_ue_id_note_validation_ue_seq OWNED BY public.note_validation_ue.id_note_validation_ue;


--
-- Name: operation_par_au; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.operation_par_au (
    id_operation_par_au bigint NOT NULL,
    id_au bigint NOT NULL,
    date_resultats_avant_repechage date,
    id_user_date_resultats_avant_repechage bigint,
    date_liste_repechage date,
    id_user_date_liste_repechage bigint,
    date_resultats_avant_deliberation date,
    id_user_date_resultats_avant_deliberation bigint,
    date_resultats_definitifs date,
    id_user_date_resultats_definitifs bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.operation_par_au OWNER TO postgres;

--
-- Name: operation_par_au_id_operation_par_au_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.operation_par_au_id_operation_par_au_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.operation_par_au_id_operation_par_au_seq OWNER TO postgres;

--
-- Name: operation_par_au_id_operation_par_au_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.operation_par_au_id_operation_par_au_seq OWNED BY public.operation_par_au.id_operation_par_au;


--
-- Name: operation_par_deliberation; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.operation_par_deliberation (
    id_operation_sur_deliberation bigint NOT NULL,
    id_au bigint NOT NULL,
    id_parcours bigint NOT NULL,
    id_niveau bigint NOT NULL,
    date_ouverture_deliberation date NOT NULL,
    date_cloture_deliberation date,
    id_user_date_ouverture_deliberation bigint NOT NULL,
    id_user_date_cloture_deliberation bigint
);


ALTER TABLE public.operation_par_deliberation OWNER TO postgres;

--
-- Name: operation_par_deliberation_id_operation_sur_deliberation_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.operation_par_deliberation_id_operation_sur_deliberation_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.operation_par_deliberation_id_operation_sur_deliberation_seq OWNER TO postgres;

--
-- Name: operation_par_deliberation_id_operation_sur_deliberation_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.operation_par_deliberation_id_operation_sur_deliberation_seq OWNED BY public.operation_par_deliberation.id_operation_sur_deliberation;


--
-- Name: operation_par_examen; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.operation_par_examen (
    id_operation_par_examen bigint NOT NULL,
    date_ouverture_saisie_note date,
    id_user_date_ouverture_saisie_note bigint,
    date_cloture_saisie_note date,
    id_user_date_cloture_saisie_note bigint,
    date_ouverture_verification_note date,
    id_user_date_ouverture_verification_note bigint,
    date_cloture_verification_note date,
    id_user_date_cloture_verification_note bigint,
    date_ouverture_saisie_en_tete date,
    id_user_date_ouverture_saisie_en_tete bigint,
    date_cloture_saisie_en_tete date,
    id_user_date_cloture_saisie_en_tete bigint,
    date_ouverture_verification_en_tete date,
    id_user_date_ouverture_verification_en_tete bigint,
    date_cloture_verification_en_tete date,
    id_user_date_cloture_verification_en_tete bigint,
    date_resultats date,
    id_user_date_resultats bigint,
    id_examen_par_au bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.operation_par_examen OWNER TO postgres;

--
-- Name: operation_par_examen_id_operation_par_examen_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.operation_par_examen_id_operation_par_examen_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.operation_par_examen_id_operation_par_examen_seq OWNER TO postgres;

--
-- Name: operation_par_examen_id_operation_par_examen_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.operation_par_examen_id_operation_par_examen_seq OWNED BY public.operation_par_examen.id_operation_par_examen;


--
-- Name: operation_par_import_resultat_paces; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.operation_par_import_resultat_paces (
    id_operation_par_import_resultat_paces bigint NOT NULL,
    date_import date NOT NULL,
    id_user_date_import bigint NOT NULL,
    id_au bigint NOT NULL,
    id_parcours bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.operation_par_import_resultat_paces OWNER TO postgres;

--
-- Name: operation_par_import_resultat_id_operation_par_import_resul_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.operation_par_import_resultat_id_operation_par_import_resul_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.operation_par_import_resultat_id_operation_par_import_resul_seq OWNER TO postgres;

--
-- Name: operation_par_import_resultat_id_operation_par_import_resul_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.operation_par_import_resultat_id_operation_par_import_resul_seq OWNED BY public.operation_par_import_resultat_paces.id_operation_par_import_resultat_paces;


--
-- Name: operation_sur_examen_par_au; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.operation_sur_examen_par_au (
    id_operation_sur_examen_par_au bigint NOT NULL,
    date_liste_repechage date,
    id_user_date_liste_repechage bigint,
    date_resultat_avant_deliberation date,
    id_user_date_resultat_avant_deliberation bigint,
    date_deliberation date,
    id_user_date_deliberation bigint,
    date_resultat_definitif date,
    id_user_date_resultat_definitif bigint,
    id_au bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.operation_sur_examen_par_au OWNER TO postgres;

--
-- Name: operation_sur_examen_par_au_id_operation_sur_examen_par_au_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.operation_sur_examen_par_au_id_operation_sur_examen_par_au_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.operation_sur_examen_par_au_id_operation_sur_examen_par_au_seq OWNER TO postgres;

--
-- Name: operation_sur_examen_par_au_id_operation_sur_examen_par_au_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.operation_sur_examen_par_au_id_operation_sur_examen_par_au_seq OWNED BY public.operation_sur_examen_par_au.id_operation_sur_examen_par_au;


--
-- Name: parcours; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.parcours (
    id_parcours bigint NOT NULL,
    nom_parcours character varying(255) NOT NULL,
    id_mention bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.parcours OWNER TO postgres;

--
-- Name: parcours_ent; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.parcours_ent (
    id_parcours_ent bigint NOT NULL,
    nom_parcours_ent character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.parcours_ent OWNER TO postgres;

--
-- Name: parcours_ent_id_parcours_ent_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.parcours_ent_id_parcours_ent_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.parcours_ent_id_parcours_ent_seq OWNER TO postgres;

--
-- Name: parcours_ent_id_parcours_ent_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.parcours_ent_id_parcours_ent_seq OWNED BY public.parcours_ent.id_parcours_ent;


--
-- Name: parcours_id_parcours_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.parcours_id_parcours_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.parcours_id_parcours_seq OWNER TO postgres;

--
-- Name: parcours_id_parcours_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.parcours_id_parcours_seq OWNED BY public.parcours.id_parcours;


--
-- Name: parcours_niveau; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.parcours_niveau (
    id_niveau bigint NOT NULL,
    id_parcours bigint NOT NULL
);


ALTER TABLE public.parcours_niveau OWNER TO postgres;

--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.personal_access_tokens_id_seq OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: pourcentage_admission; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pourcentage_admission (
    id_pourcentage_admission bigint NOT NULL,
    pourcentage_admission double precision NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.pourcentage_admission OWNER TO postgres;

--
-- Name: pourcentage_admission_id_pourcentage_admission_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.pourcentage_admission_id_pourcentage_admission_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pourcentage_admission_id_pourcentage_admission_seq OWNER TO postgres;

--
-- Name: pourcentage_admission_id_pourcentage_admission_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.pourcentage_admission_id_pourcentage_admission_seq OWNED BY public.pourcentage_admission.id_pourcentage_admission;


--
-- Name: province; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.province (
    id_province bigint NOT NULL,
    nom_province character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.province OWNER TO postgres;

--
-- Name: province_id_province_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.province_id_province_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.province_id_province_seq OWNER TO postgres;

--
-- Name: province_id_province_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.province_id_province_seq OWNED BY public.province.id_province;


--
-- Name: resultats_avant_deliberation; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.resultats_avant_deliberation (
    id_resultat_avant_deliberation bigint NOT NULL,
    id_au bigint NOT NULL,
    id_parcours bigint NOT NULL,
    id_niveau bigint NOT NULL,
    id_etudiants bigint NOT NULL,
    id_examen_par_au bigint NOT NULL,
    id_session_examen bigint NOT NULL,
    id_unite_enseignement bigint NOT NULL,
    id_element_constitutif bigint NOT NULL,
    nom_session_examen character varying(255) NOT NULL,
    type_session_retenue character varying(255) NOT NULL,
    coefficient double precision NOT NULL,
    im character varying(255) NOT NULL,
    note_ue double precision NOT NULL,
    note_ec double precision NOT NULL,
    valide character varying(255) NOT NULL,
    statut character varying(255) NOT NULL,
    a_passe_examen boolean,
    date_annulation_inscription timestamp(0) without time zone,
    total double precision NOT NULL,
    total_coefficient double precision NOT NULL,
    moyenne_passage double precision NOT NULL,
    moyenne double precision NOT NULL,
    nombre_ue integer NOT NULL,
    nombre_ue_a_valider integer NOT NULL,
    nombre_ue_validees integer NOT NULL,
    nombre_note_eliminatoire integer NOT NULL,
    statut_au_suivante character varying(255) NOT NULL,
    id_niveau_suivant bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.resultats_avant_deliberation OWNER TO postgres;

--
-- Name: resultats_avant_deliberation_id_resultat_avant_deliberation_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.resultats_avant_deliberation_id_resultat_avant_deliberation_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.resultats_avant_deliberation_id_resultat_avant_deliberation_seq OWNER TO postgres;

--
-- Name: resultats_avant_deliberation_id_resultat_avant_deliberation_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.resultats_avant_deliberation_id_resultat_avant_deliberation_seq OWNED BY public.resultats_avant_deliberation.id_resultat_avant_deliberation;


--
-- Name: resultats_avant_repechage; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.resultats_avant_repechage (
    id_resultats_avant_repechage bigint NOT NULL,
    id_note_eval bigint NOT NULL,
    id_au bigint NOT NULL,
    id_parcours bigint NOT NULL,
    id_niveau bigint NOT NULL,
    id_examen_par_au bigint NOT NULL,
    id_session_examen bigint NOT NULL,
    nom_session_examen character varying(255) NOT NULL,
    type_session character varying(255) NOT NULL,
    date_annulation_inscription date,
    coefficient double precision NOT NULL,
    id_unite_enseignement bigint NOT NULL,
    id_ue_ec bigint NOT NULL,
    id_element_constitutif bigint NOT NULL,
    im character varying(255),
    id_etudiants bigint,
    note_ec double precision NOT NULL,
    note_ue double precision NOT NULL,
    valide character varying(255) NOT NULL,
    total double precision NOT NULL,
    total_coefficient double precision NOT NULL,
    moyenne double precision NOT NULL,
    nombre_ue integer NOT NULL,
    nombre_ue_validees integer NOT NULL,
    nombre_ue_a_valider integer NOT NULL,
    nombre_note_eliminatoire integer NOT NULL,
    decision character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT resultats_avant_repechage_decision_check CHECK (((decision)::text = ANY ((ARRAY['valide'::character varying, 'repechage'::character varying])::text[]))),
    CONSTRAINT resultats_avant_repechage_valide_check CHECK (((valide)::text = ANY ((ARRAY['E'::character varying, 'N'::character varying, 'V'::character varying])::text[])))
);


ALTER TABLE public.resultats_avant_repechage OWNER TO postgres;

--
-- Name: resultats_avant_repechage_id_resultats_avant_repechage_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.resultats_avant_repechage_id_resultats_avant_repechage_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.resultats_avant_repechage_id_resultats_avant_repechage_seq OWNER TO postgres;

--
-- Name: resultats_avant_repechage_id_resultats_avant_repechage_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.resultats_avant_repechage_id_resultats_avant_repechage_seq OWNED BY public.resultats_avant_repechage.id_resultats_avant_repechage;


--
-- Name: resultats_definitifs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.resultats_definitifs (
    id_resultat_definitif bigint NOT NULL,
    id_au bigint NOT NULL,
    id_parcours bigint NOT NULL,
    id_niveau bigint NOT NULL,
    cycle integer NOT NULL,
    nom_niveau character varying(255) NOT NULL,
    rang integer NOT NULL,
    nom_niveau_long character varying(255),
    id_examen_par_au bigint NOT NULL,
    id_session_examen bigint NOT NULL,
    nom_session_examen character varying(255) NOT NULL,
    type_session_retenue character varying(255) NOT NULL,
    id_etudiants bigint NOT NULL,
    im character varying(255) NOT NULL,
    date_annulation_inscription date,
    statut character varying(255) NOT NULL,
    id_ue bigint NOT NULL,
    coef double precision NOT NULL,
    id_ec bigint NOT NULL,
    note_ue double precision NOT NULL,
    note_ec double precision NOT NULL,
    valide character varying(255) NOT NULL,
    total double precision NOT NULL,
    total_coefficient double precision NOT NULL,
    moyenne double precision NOT NULL,
    moyenne_passage double precision NOT NULL,
    nombre_ue integer NOT NULL,
    nombre_ue_a_valider integer NOT NULL,
    nombre_ue_validees integer NOT NULL,
    nombre_note_elim integer NOT NULL,
    statut_au_suivante character varying(255) NOT NULL,
    id_niveau_suivant bigint,
    intitule character varying(255) NOT NULL,
    nom_parcours character varying(255),
    nom_niveau_suivant character varying(255),
    rang_suivant integer,
    cycle_suivant character varying(255),
    nom_etudiant character varying(255) NOT NULL,
    prenoms character varying(255) NOT NULL,
    date_naissance date NOT NULL,
    lieu_naissance character varying(255) NOT NULL,
    nom_unite_enseignement character varying(255) NOT NULL,
    nom_element_constitutif character varying(255) NOT NULL,
    a_passe_examen boolean,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    a_ete_delibere boolean
);


ALTER TABLE public.resultats_definitifs OWNER TO postgres;

--
-- Name: resultats_definitifs_id_resultat_definitif_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.resultats_definitifs_id_resultat_definitif_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.resultats_definitifs_id_resultat_definitif_seq OWNER TO postgres;

--
-- Name: resultats_definitifs_id_resultat_definitif_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.resultats_definitifs_id_resultat_definitif_seq OWNED BY public.resultats_definitifs.id_resultat_definitif;


--
-- Name: role; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role (
    id bigint NOT NULL,
    nom_role character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    rang_role integer NOT NULL
);


ALTER TABLE public.role OWNER TO postgres;

--
-- Name: role_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.role_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.role_id_seq OWNER TO postgres;

--
-- Name: role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.role_id_seq OWNED BY public.role.id;


--
-- Name: selectionnes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.selectionnes (
    id_selectionnes bigint NOT NULL,
    nom character varying(255) NOT NULL,
    prenoms character varying(255),
    num_bacc character varying(255) NOT NULL,
    id_parcours bigint NOT NULL,
    id_au bigint NOT NULL,
    est_inscrit boolean,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    id_agent bigint NOT NULL
);


ALTER TABLE public.selectionnes OWNER TO postgres;

--
-- Name: selectionnes_id_selectionnes_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.selectionnes_id_selectionnes_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.selectionnes_id_selectionnes_seq OWNER TO postgres;

--
-- Name: selectionnes_id_selectionnes_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.selectionnes_id_selectionnes_seq OWNED BY public.selectionnes.id_selectionnes;


--
-- Name: serie; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.serie (
    id_serie bigint NOT NULL,
    nom_serie character varying(50) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.serie OWNER TO postgres;

--
-- Name: serie_id_serie_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.serie_id_serie_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.serie_id_serie_seq OWNER TO postgres;

--
-- Name: serie_id_serie_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.serie_id_serie_seq OWNED BY public.serie.id_serie;


--
-- Name: session_examen; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.session_examen (
    id_session_examen bigint NOT NULL,
    nom_session_examen character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    type_session character varying(255) NOT NULL,
    CONSTRAINT session_examen_type_session_check CHECK (((type_session)::text = ANY ((ARRAY['conc'::character varying, 'eval'::character varying, 'repe'::character varying])::text[])))
);


ALTER TABLE public.session_examen OWNER TO postgres;

--
-- Name: session_examen_id_session_examen_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.session_examen_id_session_examen_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.session_examen_id_session_examen_seq OWNER TO postgres;

--
-- Name: session_examen_id_session_examen_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.session_examen_id_session_examen_seq OWNED BY public.session_examen.id_session_examen;


--
-- Name: transferts_autorises; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.transferts_autorises (
    id_parcours bigint NOT NULL,
    id_niveau bigint NOT NULL
);


ALTER TABLE public.transferts_autorises OWNER TO postgres;

--
-- Name: ue_ec_parcours_niveau_au; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ue_ec_parcours_niveau_au (
    id_ue_ec bigint NOT NULL,
    coefficient double precision NOT NULL,
    id_examen_par_au bigint NOT NULL,
    id_parcours bigint NOT NULL,
    id_niveau bigint NOT NULL,
    id_unite_enseignement bigint NOT NULL,
    id_element_constitutif bigint NOT NULL,
    id_au bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.ue_ec_parcours_niveau_au OWNER TO postgres;

--
-- Name: ue_ec_parcours_niveau_au_id_ue_ec_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ue_ec_parcours_niveau_au_id_ue_ec_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ue_ec_parcours_niveau_au_id_ue_ec_seq OWNER TO postgres;

--
-- Name: ue_ec_parcours_niveau_au_id_ue_ec_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ue_ec_parcours_niveau_au_id_ue_ec_seq OWNED BY public.ue_ec_parcours_niveau_au.id_ue_ec;


--
-- Name: unite_enseignement; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.unite_enseignement (
    id_unite_enseignement bigint NOT NULL,
    nom_unite_enseignement character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.unite_enseignement OWNER TO postgres;

--
-- Name: unite_enseignement_id_unite_enseignement_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.unite_enseignement_id_unite_enseignement_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unite_enseignement_id_unite_enseignement_seq OWNER TO postgres;

--
-- Name: unite_enseignement_id_unite_enseignement_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.unite_enseignement_id_unite_enseignement_seq OWNED BY public.unite_enseignement.id_unite_enseignement;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    id_role bigint NOT NULL,
    date_suppr timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: users_valides; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.users_valides AS
 SELECT users.id,
    users.name,
    users.email,
    users.email_verified_at,
    users.password,
    users.remember_token,
    users.created_at,
    users.updated_at,
    users.id_role,
    users.date_suppr
   FROM (public.users
     JOIN public.role ON ((users.id_role = role.id)))
  WHERE ((users.date_suppr IS NULL) OR ((role.nom_role)::text = 'admin'::text));


ALTER TABLE public.users_valides OWNER TO postgres;

--
-- Name: v_inscrits2; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.v_inscrits2 AS
 SELECT i.id_au,
    e.id_parcours,
    i.id_niveau,
    i.id_inscription,
    e.id_etudiants,
    e.im,
    i.date_annulation,
    i.statut,
    i.a_passe_examen
   FROM (public.inscription i
     JOIN public.etudiants e ON ((i.id_etudiant = e.id_etudiants)))
  WITH NO DATA;


ALTER TABLE public.v_inscrits2 OWNER TO postgres;

--
-- Name: v_ue_ec_eval; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_ue_ec_eval AS
 SELECT ue_ec.id_ue_ec,
    ue_ec.coefficient,
    ue_ec.id_examen_par_au,
    ue_ec.id_parcours,
    ue_ec.id_niveau,
    ue_ec.id_unite_enseignement,
    ue_ec.id_element_constitutif,
    ue_ec.id_au,
    ue_ec.created_at,
    ue_ec.updated_at,
    se.id_session_examen,
    se.nom_session_examen,
    se.type_session
   FROM ((public.ue_ec_parcours_niveau_au ue_ec
     JOIN public.examen_par_au epa ON ((ue_ec.id_examen_par_au = epa.id_examen_par_au)))
     JOIN public.session_examen se ON ((epa.id_session_examen = se.id_session_examen)))
  WHERE (((se.type_session)::text = 'eval'::text) OR ((se.type_session)::text = 'repe'::text));


ALTER TABLE public.v_ue_ec_eval OWNER TO postgres;

--
-- Name: v_association_etu_ec; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_association_etu_ec AS
 SELECT ue_ec.id_au,
    ue_ec.id_parcours,
    ue_ec.id_niveau,
    ue_ec.coefficient,
    ue_ec.id_unite_enseignement,
    ue_ec.id_ue_ec,
    ue_ec.id_element_constitutif,
    ue_ec.id_examen_par_au,
    ue_ec.id_session_examen,
    ue_ec.nom_session_examen,
    ue_ec.type_session,
    i.id_etudiants,
    i.im,
    i.date_annulation,
    i.statut,
    i.a_passe_examen
   FROM (public.v_ue_ec_eval ue_ec
     LEFT JOIN public.v_inscrits2 i ON (((i.id_parcours = ue_ec.id_parcours) AND (i.id_niveau = ue_ec.id_niveau) AND (i.id_au = ue_ec.id_au))));


ALTER TABLE public.v_association_etu_ec OWNER TO postgres;

--
-- Name: v_correspondance_note_matricule; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_correspondance_note_matricule AS
 SELECT m.id_ue_ec AS id_ue_ec_matricule,
    m.numero AS numero_matricule,
    m.matricule,
    n.id_ue_ec AS id_ue_ec_note,
    n.numero AS numero_note,
    n.note,
    ue_ec.id_ue_ec,
    ue_ec.coefficient,
    ue_ec.id_examen_par_au,
    ue_ec.id_parcours,
    ue_ec.id_niveau,
    ue_ec.id_unite_enseignement,
    ue_ec.id_element_constitutif,
    ue_ec.id_au,
    ue_ec.created_at,
    ue_ec.updated_at
   FROM ((public.barcode_note n
     FULL JOIN public.barcode_matricule m ON (((n.id_ue_ec = m.id_ue_ec) AND (n.numero = m.numero))))
     JOIN public.ue_ec_parcours_niveau_au ue_ec ON (((n.id_ue_ec = ue_ec.id_ue_ec) OR (m.id_ue_ec = ue_ec.id_ue_ec))));


ALTER TABLE public.v_correspondance_note_matricule OWNER TO postgres;

--
-- Name: v_note; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_note AS
 SELECT a.id_au,
    a.id_parcours,
    a.id_niveau,
    a.coefficient,
    a.id_unite_enseignement,
    a.id_ue_ec,
    a.id_element_constitutif,
    a.id_examen_par_au,
    a.id_session_examen,
    a.nom_session_examen,
    a.type_session,
    a.im,
    a.id_etudiants,
    COALESCE(n.note, (0)::double precision) AS note,
    a.date_annulation,
    a.statut,
    a.a_passe_examen
   FROM (public.v_correspondance_note_matricule n
     RIGHT JOIN public.v_association_etu_ec a ON (((a.id_ue_ec = n.id_ue_ec) AND ((a.im)::text = (n.matricule)::text))));


ALTER TABLE public.v_note OWNER TO postgres;

--
-- Name: v_note_moyenne_ue_rep; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_note_moyenne_ue_rep AS
 SELECT v_note.id_au,
    v_note.id_parcours,
    v_note.id_niveau,
    v_note.coefficient,
    v_note.id_unite_enseignement,
    v_note.id_examen_par_au,
    v_note.id_session_examen,
    v_note.im,
    v_note.id_etudiants,
    avg(v_note.note) AS note,
    v_note.date_annulation,
    v_note.statut,
    v_note.a_passe_examen
   FROM public.v_note
  WHERE ((v_note.type_session)::text = 'repe'::text)
  GROUP BY v_note.id_au, v_note.id_parcours, v_note.id_niveau, v_note.coefficient, v_note.id_unite_enseignement, v_note.id_examen_par_au, v_note.id_session_examen, v_note.im, v_note.id_etudiants, v_note.date_annulation, v_note.statut, v_note.a_passe_examen;


ALTER TABLE public.v_note_moyenne_ue_rep OWNER TO postgres;

--
-- Name: v_note_ue_avec_ec_rep; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_note_ue_avec_ec_rep AS
 SELECT n.id_au,
    n.id_parcours,
    n.id_niveau,
    n.id_examen_par_au,
    n.id_session_examen,
    n.nom_session_examen,
    n.type_session,
    n.date_annulation,
    n.coefficient,
    n.id_unite_enseignement,
    n.id_ue_ec,
    n.id_element_constitutif,
    n.im,
    n.id_etudiants,
    n.note AS note_ec,
    v.note AS note_ue,
    n.statut,
    n.a_passe_examen
   FROM (public.v_note_moyenne_ue_rep v
     JOIN public.v_note n ON ((((n.id_au = v.id_au) AND (n.id_parcours = v.id_parcours) AND (n.id_niveau = v.id_niveau) AND (n.id_unite_enseignement = v.id_unite_enseignement) AND (n.id_examen_par_au = v.id_examen_par_au) AND (n.id_etudiants = v.id_etudiants)) OR ((n.id_au = v.id_au) AND (n.id_parcours = v.id_parcours) AND (n.id_niveau = v.id_niveau) AND (n.id_unite_enseignement = v.id_unite_enseignement) AND (n.id_examen_par_au = v.id_examen_par_au) AND (n.id_etudiants IS NULL) AND (v.id_etudiants IS NULL)))));


ALTER TABLE public.v_note_ue_avec_ec_rep OWNER TO postgres;

--
-- Name: v_assemblage_eval_repe; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_assemblage_eval_repe AS
 SELECT eval.id_au,
    eval.id_parcours,
    eval.id_niveau,
    eval.id_examen_par_au,
    eval.id_session_examen,
    eval.nom_session_examen,
        CASE
            WHEN (rep.note_ue >= eval.note_ue) THEN rep.type_session
            WHEN (eval.note_ue > rep.note_ue) THEN eval.type_session
            ELSE NULL::character varying
        END AS type_session_retenue,
    eval.date_annulation_inscription,
    eval.coefficient,
    eval.id_unite_enseignement,
    eval.id_element_constitutif,
    eval.im,
    eval.id_etudiants,
    GREATEST(rep.note_ue, eval.note_ue) AS note_ue,
        CASE
            WHEN (rep.note_ue >= eval.note_ue) THEN rep.note_ec
            WHEN (eval.note_ue > rep.note_ue) THEN eval.note_ec
            ELSE NULL::double precision
        END AS note_ec,
    rep.statut,
    rep.a_passe_examen
   FROM (public.v_note_ue_avec_ec_rep rep
     JOIN public.resultats_avant_repechage eval ON ((((rep.id_au = eval.id_au) AND (rep.id_parcours = eval.id_parcours) AND (rep.id_niveau = eval.id_niveau) AND (rep.id_unite_enseignement = eval.id_unite_enseignement) AND (rep.id_element_constitutif = eval.id_element_constitutif) AND (rep.id_etudiants = eval.id_etudiants)) OR ((rep.id_au = eval.id_au) AND (rep.id_parcours = eval.id_parcours) AND (rep.id_niveau = eval.id_niveau) AND (rep.id_unite_enseignement = eval.id_unite_enseignement) AND (rep.id_element_constitutif = eval.id_element_constitutif) AND (rep.id_etudiants IS NULL) AND (eval.id_etudiants IS NULL)))));


ALTER TABLE public.v_assemblage_eval_repe OWNER TO postgres;

--
-- Name: v_liste_ue; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_liste_ue AS
 SELECT DISTINCT ON (ue_ec.id_au, ue_ec.id_parcours, ue_ec.id_niveau, ue_ec.id_unite_enseignement, epa.id_examen_par_au) ue_ec.id_au,
    ue_ec.id_parcours,
    ue_ec.id_niveau,
    ue_ec.id_unite_enseignement
   FROM ((public.ue_ec_parcours_niveau_au ue_ec
     JOIN public.examen_par_au epa ON ((ue_ec.id_examen_par_au = epa.id_examen_par_au)))
     JOIN public.session_examen se ON ((epa.id_session_examen = se.id_session_examen)))
  WHERE ((se.type_session)::text = 'eval'::text)
  ORDER BY ue_ec.id_au DESC, ue_ec.id_parcours, ue_ec.id_niveau, ue_ec.id_unite_enseignement, epa.id_examen_par_au, ue_ec.id_ue_ec;


ALTER TABLE public.v_liste_ue OWNER TO postgres;

--
-- Name: v_note_ue_rep; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_note_ue_rep AS
 SELECT DISTINCT ON (v_assemblage_eval_repe.id_au, v_assemblage_eval_repe.id_parcours, v_assemblage_eval_repe.id_niveau, v_assemblage_eval_repe.id_examen_par_au, v_assemblage_eval_repe.id_unite_enseignement, v_assemblage_eval_repe.id_etudiants) v_assemblage_eval_repe.id_au,
    v_assemblage_eval_repe.id_parcours,
    v_assemblage_eval_repe.id_niveau,
    v_assemblage_eval_repe.id_examen_par_au,
    v_assemblage_eval_repe.id_session_examen,
    v_assemblage_eval_repe.nom_session_examen,
    v_assemblage_eval_repe.type_session_retenue,
    v_assemblage_eval_repe.date_annulation_inscription,
    v_assemblage_eval_repe.coefficient,
    v_assemblage_eval_repe.id_unite_enseignement,
    v_assemblage_eval_repe.im,
    v_assemblage_eval_repe.id_etudiants,
    v_assemblage_eval_repe.note_ue,
    v_assemblage_eval_repe.statut,
    v_assemblage_eval_repe.a_passe_examen
   FROM public.v_assemblage_eval_repe
  ORDER BY v_assemblage_eval_repe.id_au, v_assemblage_eval_repe.id_parcours, v_assemblage_eval_repe.id_niveau, v_assemblage_eval_repe.id_examen_par_au, v_assemblage_eval_repe.id_unite_enseignement, v_assemblage_eval_repe.id_etudiants, v_assemblage_eval_repe.id_element_constitutif;


ALTER TABLE public.v_note_ue_rep OWNER TO postgres;

--
-- Name: v_total_note_rep; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_total_note_rep AS
 SELECT v_note_ue_rep.id_au,
    v_note_ue_rep.id_parcours,
    v_note_ue_rep.id_niveau,
    v_note_ue_rep.date_annulation_inscription,
    v_note_ue_rep.im,
    v_note_ue_rep.id_etudiants,
    sum((v_note_ue_rep.note_ue * v_note_ue_rep.coefficient)) AS total,
    sum(v_note_ue_rep.coefficient) AS total_coefficient,
    v_note_ue_rep.statut,
    v_note_ue_rep.a_passe_examen
   FROM public.v_note_ue_rep
  GROUP BY v_note_ue_rep.id_au, v_note_ue_rep.id_parcours, v_note_ue_rep.id_niveau, v_note_ue_rep.date_annulation_inscription, v_note_ue_rep.im, v_note_ue_rep.id_etudiants, v_note_ue_rep.statut, v_note_ue_rep.a_passe_examen;


ALTER TABLE public.v_total_note_rep OWNER TO postgres;

--
-- Name: v_moyenne_rep; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_moyenne_rep AS
 SELECT v_total_note_rep.id_au,
    v_total_note_rep.id_parcours,
    v_total_note_rep.id_niveau,
    v_total_note_rep.date_annulation_inscription,
    v_total_note_rep.im,
    v_total_note_rep.id_etudiants,
    v_total_note_rep.total,
    v_total_note_rep.total_coefficient,
    (v_total_note_rep.total / v_total_note_rep.total_coefficient) AS moyenne,
    v_total_note_rep.statut,
    v_total_note_rep.a_passe_examen
   FROM public.v_total_note_rep;


ALTER TABLE public.v_moyenne_rep OWNER TO postgres;

--
-- Name: v_validation_ue_rep; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_validation_ue_rep AS
 SELECT v_note_ue_rep.id_au,
    v_note_ue_rep.id_parcours,
    v_note_ue_rep.id_niveau,
    v_note_ue_rep.id_examen_par_au,
    v_note_ue_rep.id_session_examen,
    v_note_ue_rep.nom_session_examen,
    v_note_ue_rep.type_session_retenue,
    v_note_ue_rep.date_annulation_inscription,
    v_note_ue_rep.coefficient,
    v_note_ue_rep.id_unite_enseignement,
    v_note_ue_rep.im,
    v_note_ue_rep.id_etudiants,
    v_note_ue_rep.note_ue,
    v_note_ue_rep.statut,
    v_note_ue_rep.a_passe_examen,
        CASE
            WHEN (v_note_ue_rep.note_ue >= ( SELECT note_validation_ue.note_validation_ue AS moyenne
               FROM public.note_validation_ue
              ORDER BY note_validation_ue.id_note_validation_ue DESC
             LIMIT 1)) THEN 'V'::text
            WHEN ((v_note_ue_rep.note_ue > (( SELECT note_eliminatoire.note_elim
               FROM public.note_eliminatoire
              ORDER BY note_eliminatoire.id_note_eliminatoire DESC
             LIMIT 1))::double precision) AND (v_note_ue_rep.note_ue < ( SELECT note_validation_ue.note_validation_ue
               FROM public.note_validation_ue
              ORDER BY note_validation_ue.id_note_validation_ue DESC
             LIMIT 1))) THEN 'N'::text
            ELSE 'E'::text
        END AS valide
   FROM public.v_note_ue_rep;


ALTER TABLE public.v_validation_ue_rep OWNER TO postgres;

--
-- Name: v_nombre_note_eliminatoire_rep; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_nombre_note_eliminatoire_rep AS
 SELECT v_validation_ue_rep.id_au,
    v_validation_ue_rep.id_parcours,
    v_validation_ue_rep.id_niveau,
    v_validation_ue_rep.im,
    v_validation_ue_rep.id_etudiants,
    count(v_validation_ue_rep.valide) AS nombre_note_eliminatoire
   FROM public.v_validation_ue_rep
  WHERE (v_validation_ue_rep.valide = 'E'::text)
  GROUP BY v_validation_ue_rep.id_au, v_validation_ue_rep.id_parcours, v_validation_ue_rep.id_niveau, v_validation_ue_rep.im, v_validation_ue_rep.id_etudiants;


ALTER TABLE public.v_nombre_note_eliminatoire_rep OWNER TO postgres;

--
-- Name: v_nombre_ue; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_nombre_ue AS
 SELECT v_liste_ue.id_au,
    v_liste_ue.id_parcours,
    v_liste_ue.id_niveau,
    count(v_liste_ue.id_unite_enseignement) AS nombre_ue
   FROM public.v_liste_ue
  GROUP BY v_liste_ue.id_au, v_liste_ue.id_parcours, v_liste_ue.id_niveau;


ALTER TABLE public.v_nombre_ue OWNER TO postgres;

--
-- Name: v_nombre_ue_a_valider; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_nombre_ue_a_valider AS
 SELECT v_nombre_ue.id_au,
    v_nombre_ue.id_parcours,
    v_nombre_ue.id_niveau,
    v_nombre_ue.nombre_ue,
    floor((((v_nombre_ue.nombre_ue)::double precision * ( SELECT pourcentage_admission.pourcentage_admission
           FROM public.pourcentage_admission
          ORDER BY pourcentage_admission.id_pourcentage_admission DESC
         LIMIT 1)) / (100)::double precision)) AS nombre_ue_a_valider
   FROM public.v_nombre_ue;


ALTER TABLE public.v_nombre_ue_a_valider OWNER TO postgres;

--
-- Name: v_nombre_ue_validees_rep; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_nombre_ue_validees_rep AS
 SELECT v_validation_ue_rep.id_au,
    v_validation_ue_rep.id_parcours,
    v_validation_ue_rep.id_niveau,
    v_validation_ue_rep.im,
    v_validation_ue_rep.id_etudiants,
    count(v_validation_ue_rep.valide) AS nombre_ue_validees
   FROM public.v_validation_ue_rep
  WHERE (v_validation_ue_rep.valide = 'V'::text)
  GROUP BY v_validation_ue_rep.id_au, v_validation_ue_rep.id_parcours, v_validation_ue_rep.id_niveau, v_validation_ue_rep.im, v_validation_ue_rep.id_etudiants, v_validation_ue_rep.statut;


ALTER TABLE public.v_nombre_ue_validees_rep OWNER TO postgres;

--
-- Name: v_nombre_ue_validees_sans_inscrits_rep; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_nombre_ue_validees_sans_inscrits_rep AS
 SELECT DISTINCT ON (v_validation_ue_rep.id_au, v_validation_ue_rep.id_parcours, v_validation_ue_rep.id_niveau) v_validation_ue_rep.id_au,
    v_validation_ue_rep.id_parcours,
    v_validation_ue_rep.id_niveau,
    v_validation_ue_rep.im,
    v_validation_ue_rep.id_etudiants,
    0 AS nombre_ue_validees
   FROM public.v_validation_ue_rep
  WHERE (v_validation_ue_rep.id_etudiants IS NULL)
  ORDER BY v_validation_ue_rep.id_au, v_validation_ue_rep.id_parcours, v_validation_ue_rep.id_niveau, v_validation_ue_rep.id_examen_par_au, v_validation_ue_rep.id_unite_enseignement;


ALTER TABLE public.v_nombre_ue_validees_sans_inscrits_rep OWNER TO postgres;

--
-- Name: v_nombre_ue_validees_complet_rep; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_nombre_ue_validees_complet_rep AS
 SELECT v_nombre_ue_validees_rep.id_au,
    v_nombre_ue_validees_rep.id_parcours,
    v_nombre_ue_validees_rep.id_niveau,
    v_nombre_ue_validees_rep.im,
    v_nombre_ue_validees_rep.id_etudiants,
    v_nombre_ue_validees_rep.nombre_ue_validees
   FROM public.v_nombre_ue_validees_rep
UNION ALL
 SELECT v_nombre_ue_validees_sans_inscrits_rep.id_au,
    v_nombre_ue_validees_sans_inscrits_rep.id_parcours,
    v_nombre_ue_validees_sans_inscrits_rep.id_niveau,
    v_nombre_ue_validees_sans_inscrits_rep.im,
    v_nombre_ue_validees_sans_inscrits_rep.id_etudiants,
    v_nombre_ue_validees_sans_inscrits_rep.nombre_ue_validees
   FROM public.v_nombre_ue_validees_sans_inscrits_rep;


ALTER TABLE public.v_nombre_ue_validees_complet_rep OWNER TO postgres;

--
-- Name: v_conditions_passage; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_conditions_passage AS
 SELECT m.id_au,
    m.id_parcours,
    m.id_niveau,
    m.im,
    m.id_etudiants,
    m.date_annulation_inscription,
    m.statut,
    m.a_passe_examen,
    m.total,
    m.total_coefficient,
    m.moyenne,
    av.nombre_ue,
    av.nombre_ue_a_valider,
    nv.nombre_ue_validees,
    ne.nombre_note_eliminatoire
   FROM (((public.v_moyenne_rep m
     JOIN public.v_nombre_ue_validees_complet_rep nv ON ((((nv.id_au = m.id_au) AND (nv.id_parcours = m.id_parcours) AND (nv.id_niveau = m.id_niveau) AND (nv.id_etudiants = m.id_etudiants)) OR ((nv.id_au = m.id_au) AND (nv.id_parcours = m.id_parcours) AND (nv.id_niveau = m.id_niveau) AND (nv.id_etudiants IS NULL) AND (m.id_etudiants IS NULL)))))
     JOIN public.v_nombre_ue_a_valider av ON (((av.id_au = m.id_au) AND (av.id_parcours = m.id_parcours) AND (av.id_niveau = m.id_niveau))))
     JOIN public.v_nombre_note_eliminatoire_rep ne ON ((((ne.id_au = m.id_au) AND (ne.id_parcours = m.id_parcours) AND (ne.id_niveau = m.id_niveau) AND (ne.id_etudiants = m.id_etudiants)) OR ((ne.id_au = m.id_au) AND (ne.id_parcours = m.id_parcours) AND (ne.id_niveau = m.id_niveau) AND (ne.id_etudiants IS NULL) AND (m.id_etudiants IS NULL)))));


ALTER TABLE public.v_conditions_passage OWNER TO postgres;

--
-- Name: v_statut_avant_deliberation; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_statut_avant_deliberation AS
 SELECT v_conditions_passage.id_au,
    v_conditions_passage.id_parcours,
    v_conditions_passage.id_niveau,
    v_conditions_passage.im,
    v_conditions_passage.id_etudiants,
    v_conditions_passage.date_annulation_inscription,
    v_conditions_passage.statut,
    v_conditions_passage.a_passe_examen,
    v_conditions_passage.total,
    v_conditions_passage.total_coefficient,
    ( SELECT moyenne_admission.moyenne_admission
           FROM public.moyenne_admission
          ORDER BY moyenne_admission.id_moyenne_admission DESC
         LIMIT 1) AS moyenne_passage,
    v_conditions_passage.moyenne,
    v_conditions_passage.nombre_ue,
    v_conditions_passage.nombre_ue_a_valider,
    v_conditions_passage.nombre_ue_validees,
    v_conditions_passage.nombre_note_eliminatoire,
        CASE
            WHEN ((v_conditions_passage.date_annulation_inscription IS NOT NULL) AND ((v_conditions_passage.statut)::text = 'passant'::text) AND (v_conditions_passage.a_passe_examen = false)) THEN 'passant'::character varying
            WHEN ((v_conditions_passage.date_annulation_inscription IS NOT NULL) AND ((v_conditions_passage.statut)::text = 'passant'::text) AND (v_conditions_passage.a_passe_examen = false)) THEN 'redoublant'::character varying
            WHEN ((v_conditions_passage.moyenne >= ( SELECT moyenne_admission.moyenne_admission
               FROM public.moyenne_admission
              ORDER BY moyenne_admission.id_moyenne_admission DESC
             LIMIT 1)) AND ((v_conditions_passage.nombre_ue_validees)::double precision >= v_conditions_passage.nombre_ue_a_valider) AND (v_conditions_passage.nombre_note_eliminatoire = 0)) THEN 'passant'::character varying
            ELSE public.statuer(v_conditions_passage.id_etudiants, v_conditions_passage.id_niveau)
        END AS statut_au_suivante
   FROM public.v_conditions_passage;


ALTER TABLE public.v_statut_avant_deliberation OWNER TO postgres;

--
-- Name: v_niveau_suivant_avant_deliberation; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_niveau_suivant_avant_deliberation AS
 SELECT v_statut_avant_deliberation.id_au,
    v_statut_avant_deliberation.id_parcours,
    v_statut_avant_deliberation.id_niveau,
    v_statut_avant_deliberation.im,
    v_statut_avant_deliberation.id_etudiants,
    v_statut_avant_deliberation.date_annulation_inscription,
    v_statut_avant_deliberation.statut,
    v_statut_avant_deliberation.a_passe_examen,
    v_statut_avant_deliberation.total,
    v_statut_avant_deliberation.total_coefficient,
    v_statut_avant_deliberation.moyenne_passage,
    v_statut_avant_deliberation.moyenne,
    v_statut_avant_deliberation.nombre_ue,
    v_statut_avant_deliberation.nombre_ue_a_valider,
    v_statut_avant_deliberation.nombre_ue_validees,
    v_statut_avant_deliberation.nombre_note_eliminatoire,
    v_statut_avant_deliberation.statut_au_suivante,
    public.get_niveau_suivant(v_statut_avant_deliberation.statut_au_suivante, v_statut_avant_deliberation.id_niveau, v_statut_avant_deliberation.date_annulation_inscription) AS niveau_suivant
   FROM public.v_statut_avant_deliberation;


ALTER TABLE public.v_niveau_suivant_avant_deliberation OWNER TO postgres;

--
-- Name: v_calcul_resultats_avant_deliberation; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_calcul_resultats_avant_deliberation AS
 SELECT eval.id_au,
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
   FROM ((public.v_assemblage_eval_repe eval
     JOIN public.v_niveau_suivant_avant_deliberation niv ON (((eval.id_au = niv.id_au) AND (eval.id_parcours = niv.id_parcours) AND (eval.id_niveau = niv.id_niveau) AND ((eval.id_etudiants = niv.id_etudiants) OR ((eval.id_etudiants IS NULL) AND (niv.id_etudiants IS NULL))))))
     JOIN public.v_validation_ue_rep val ON (((eval.id_au = val.id_au) AND (eval.id_parcours = val.id_parcours) AND (eval.id_niveau = val.id_niveau) AND ((eval.id_etudiants = val.id_etudiants) OR ((val.id_etudiants IS NULL) AND (val.id_etudiants IS NULL))) AND (eval.id_unite_enseignement = val.id_unite_enseignement))));


ALTER TABLE public.v_calcul_resultats_avant_deliberation OWNER TO postgres;

--
-- Name: v_inscrits; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_inscrits AS
 SELECT e.id_etudiants,
    e.im,
    e.nom,
    e.prenoms,
    e.sexe,
    e.date_premiere_inscription,
    e.date_naissance,
    e.lieu_naissance,
    e.num_piece_identite,
    e.date_delivrance,
    e.lieu_delivrance,
    e.adresse,
    e.telephone,
    e.pere,
    e.profession_pere,
    e.tel_pere,
    e.adresse_pere,
    e.mere,
    e.profession_mere,
    e.tel_mere,
    e.adresse_mere,
    e.est_officier,
    e.id_parcours,
    e.created_at,
    e.updated_at,
    e.id_agent,
    e.annee_bacc,
    e.id_serie,
    e.id_province,
    e.id_nationalite,
    e.type_piece_identite,
    e.email,
    e.id_etablissement_transfert,
    e.id_au_transfert,
    e.id_niveau_transfert,
    i.id_inscription,
    i.date_inscription,
    i.date_certificat_scol,
    i.date_annulation,
    i.id_niveau,
    i.id_au,
    n.nom_niveau,
    au.intitule,
    p.nom_parcours,
    m.id_mention,
    m.nom_mention,
    n.nom_niveau_long
   FROM (((((public.etudiants e
     JOIN public.inscription i ON ((i.id_etudiant = e.id_etudiants)))
     JOIN public.niveau n ON ((i.id_niveau = n.id_niveau)))
     JOIN public.au ON ((i.id_au = au.id_au)))
     JOIN public.parcours p ON ((e.id_parcours = p.id_parcours)))
     JOIN public.mention m ON ((p.id_mention = m.id_mention)));


ALTER TABLE public.v_inscrits OWNER TO postgres;

--
-- Name: v_check_inscription_ue_ec; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_check_inscription_ue_ec AS
 SELECT i.id_etudiants,
    i.im,
    i.nom,
    i.prenoms,
    i.sexe,
    i.date_premiere_inscription,
    i.date_naissance,
    i.lieu_naissance,
    i.num_piece_identite,
    i.date_delivrance,
    i.lieu_delivrance,
    i.adresse,
    i.telephone,
    i.pere,
    i.profession_pere,
    i.tel_pere,
    i.adresse_pere,
    i.mere,
    i.profession_mere,
    i.tel_mere,
    i.adresse_mere,
    i.est_officier,
    i.id_parcours,
    i.created_at,
    i.updated_at,
    i.id_agent,
    i.annee_bacc,
    i.id_serie,
    i.id_province,
    i.id_nationalite,
    i.type_piece_identite,
    i.email,
    i.id_etablissement_transfert,
    i.id_au_transfert,
    i.id_niveau_transfert,
    i.id_inscription,
    i.date_inscription,
    i.date_certificat_scol,
    i.date_annulation,
    i.id_niveau,
    i.id_au,
    i.nom_niveau,
    i.intitule,
    i.nom_parcours,
    i.id_mention,
    i.nom_mention,
    i.nom_niveau_long,
    ue_ec.id_ue_ec,
    ue_ec.coefficient,
    ue_ec.id_examen_par_au,
    ue_ec.id_unite_enseignement,
    ue_ec.id_element_constitutif
   FROM (public.ue_ec_parcours_niveau_au ue_ec
     JOIN public.v_inscrits i ON (((ue_ec.id_parcours = i.id_parcours) AND (ue_ec.id_niveau = i.id_niveau) AND (ue_ec.id_au = i.id_au))));


ALTER TABLE public.v_check_inscription_ue_ec OWNER TO postgres;

--
-- Name: v_cj_au_parcours_niveau; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_cj_au_parcours_niveau AS
 SELECT au.id_au,
    au.intitule,
    au.ouverture,
    au.cloture,
    au.created_at,
    au.updated_at,
    parcours_niveau.id_niveau,
    parcours_niveau.id_parcours
   FROM (public.au
     CROSS JOIN public.parcours_niveau);


ALTER TABLE public.v_cj_au_parcours_niveau OWNER TO postgres;

--
-- Name: v_note_eval_ue; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_note_eval_ue AS
 SELECT DISTINCT ON (note_eval.id_au, note_eval.id_parcours, note_eval.id_niveau, note_eval.id_examen_par_au, note_eval.id_unite_enseignement, note_eval.im) note_eval.id_note_eval,
    note_eval.id_au,
    note_eval.id_parcours,
    note_eval.id_niveau,
    note_eval.id_examen_par_au,
    note_eval.id_session_examen,
    note_eval.nom_session_examen,
    note_eval.type_session,
    note_eval.date_annulation_inscription,
    note_eval.coefficient,
    note_eval.id_unite_enseignement,
    note_eval.im,
    note_eval.id_etudiants,
    note_eval.note_ue,
    note_eval.valide
   FROM public.note_eval
  ORDER BY note_eval.id_au, note_eval.id_parcours, note_eval.id_niveau, note_eval.id_examen_par_au, note_eval.id_unite_enseignement, note_eval.im, note_eval.id_ue_ec;


ALTER TABLE public.v_note_eval_ue OWNER TO postgres;

--
-- Name: v_total_note; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_total_note AS
 SELECT v_note_eval_ue.id_au,
    v_note_eval_ue.id_parcours,
    v_note_eval_ue.id_niveau,
    v_note_eval_ue.date_annulation_inscription,
    v_note_eval_ue.im,
    v_note_eval_ue.id_etudiants,
    sum(v_note_eval_ue.note_ue) AS total,
    sum(v_note_eval_ue.coefficient) AS total_coefficient
   FROM public.v_note_eval_ue
  WHERE ((v_note_eval_ue.type_session)::text = 'eval'::text)
  GROUP BY v_note_eval_ue.id_au, v_note_eval_ue.id_parcours, v_note_eval_ue.id_niveau, v_note_eval_ue.date_annulation_inscription, v_note_eval_ue.im, v_note_eval_ue.id_etudiants;


ALTER TABLE public.v_total_note OWNER TO postgres;

--
-- Name: v_moyennes; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_moyennes AS
 SELECT v_total_note.id_au,
    v_total_note.id_parcours,
    v_total_note.id_niveau,
    v_total_note.date_annulation_inscription,
    v_total_note.im,
    v_total_note.id_etudiants,
    v_total_note.total,
    v_total_note.total_coefficient,
    (v_total_note.total / v_total_note.total_coefficient) AS moyenne
   FROM public.v_total_note;


ALTER TABLE public.v_moyennes OWNER TO postgres;

--
-- Name: v_nombre_note_eliminatoire; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_nombre_note_eliminatoire AS
 SELECT v_note_eval_ue.id_au,
    v_note_eval_ue.id_parcours,
    v_note_eval_ue.id_niveau,
    v_note_eval_ue.im,
    v_note_eval_ue.id_etudiants,
    count(v_note_eval_ue.valide) AS nombre_note_eliminatoire
   FROM public.v_note_eval_ue
  WHERE ((v_note_eval_ue.valide)::text = 'E'::text)
  GROUP BY v_note_eval_ue.id_au, v_note_eval_ue.id_parcours, v_note_eval_ue.id_niveau, v_note_eval_ue.im, v_note_eval_ue.id_etudiants;


ALTER TABLE public.v_nombre_note_eliminatoire OWNER TO postgres;

--
-- Name: v_nombre_ue_validees; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_nombre_ue_validees AS
 SELECT v_note_eval_ue.id_au,
    v_note_eval_ue.id_parcours,
    v_note_eval_ue.id_niveau,
    v_note_eval_ue.im,
    v_note_eval_ue.id_etudiants,
    count(v_note_eval_ue.valide) AS nombre_ue_validees
   FROM public.v_note_eval_ue
  WHERE (((v_note_eval_ue.valide)::text = 'V'::text) AND ((v_note_eval_ue.type_session)::text = 'eval'::text))
  GROUP BY v_note_eval_ue.id_au, v_note_eval_ue.id_parcours, v_note_eval_ue.id_niveau, v_note_eval_ue.im, v_note_eval_ue.id_etudiants;


ALTER TABLE public.v_nombre_ue_validees OWNER TO postgres;

--
-- Name: v_nombre_ue_validees_sans_inscrits; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_nombre_ue_validees_sans_inscrits AS
 SELECT DISTINCT ON (v_note_eval_ue.id_au, v_note_eval_ue.id_parcours, v_note_eval_ue.id_niveau) v_note_eval_ue.id_au,
    v_note_eval_ue.id_parcours,
    v_note_eval_ue.id_niveau,
    v_note_eval_ue.im,
    v_note_eval_ue.id_etudiants,
    0 AS nombre_ue_validees
   FROM public.v_note_eval_ue
  WHERE ((v_note_eval_ue.id_etudiants IS NULL) AND ((v_note_eval_ue.type_session)::text = 'eval'::text))
  ORDER BY v_note_eval_ue.id_au, v_note_eval_ue.id_parcours, v_note_eval_ue.id_niveau, v_note_eval_ue.id_examen_par_au, v_note_eval_ue.id_unite_enseignement;


ALTER TABLE public.v_nombre_ue_validees_sans_inscrits OWNER TO postgres;

--
-- Name: v_nombre_ue_validees_complet; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_nombre_ue_validees_complet AS
 SELECT v_nombre_ue_validees.id_au,
    v_nombre_ue_validees.id_parcours,
    v_nombre_ue_validees.id_niveau,
    v_nombre_ue_validees.im,
    v_nombre_ue_validees.id_etudiants,
    v_nombre_ue_validees.nombre_ue_validees
   FROM public.v_nombre_ue_validees
UNION ALL
 SELECT v_nombre_ue_validees_sans_inscrits.id_au,
    v_nombre_ue_validees_sans_inscrits.id_parcours,
    v_nombre_ue_validees_sans_inscrits.id_niveau,
    v_nombre_ue_validees_sans_inscrits.im,
    v_nombre_ue_validees_sans_inscrits.id_etudiants,
    v_nombre_ue_validees_sans_inscrits.nombre_ue_validees
   FROM public.v_nombre_ue_validees_sans_inscrits;


ALTER TABLE public.v_nombre_ue_validees_complet OWNER TO postgres;

--
-- Name: v_resultats; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_resultats AS
 SELECT m.id_au,
    m.id_parcours,
    m.id_niveau,
    m.id_etudiants,
    m.im,
    m.total,
    m.total_coefficient,
    m.moyenne,
    v.nombre_ue,
    c.nombre_ue_validees,
    v.nombre_ue_a_valider,
    e.nombre_note_eliminatoire
   FROM (((public.v_moyennes m
     JOIN public.v_nombre_ue_a_valider v ON (((m.id_au = v.id_au) AND (m.id_parcours = v.id_parcours) AND (m.id_niveau = v.id_niveau))))
     JOIN public.v_nombre_note_eliminatoire e ON ((((m.id_au = e.id_au) AND (m.id_parcours = e.id_parcours) AND (m.id_niveau = e.id_niveau) AND (m.id_etudiants = e.id_etudiants)) OR ((m.id_au = e.id_au) AND (m.id_parcours = e.id_parcours) AND (m.id_niveau = e.id_niveau) AND (m.id_etudiants IS NULL) AND (e.id_etudiants IS NULL)))))
     JOIN public.v_nombre_ue_validees_complet c ON ((((m.id_au = c.id_au) AND (m.id_parcours = c.id_parcours) AND (m.id_niveau = c.id_niveau) AND (m.id_etudiants = c.id_etudiants)) OR ((m.id_au = c.id_au) AND (m.id_parcours = c.id_parcours) AND (m.id_niveau = c.id_niveau) AND (m.id_etudiants IS NULL) AND (c.id_etudiants IS NULL)))));


ALTER TABLE public.v_resultats OWNER TO postgres;

--
-- Name: v_decision; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_decision AS
 SELECT v_resultats.id_au,
    v_resultats.id_parcours,
    v_resultats.id_niveau,
    v_resultats.id_etudiants,
    v_resultats.im,
    v_resultats.total,
    v_resultats.total_coefficient,
    v_resultats.moyenne,
    v_resultats.nombre_ue,
    v_resultats.nombre_ue_validees,
    v_resultats.nombre_ue_a_valider,
    v_resultats.nombre_note_eliminatoire,
        CASE
            WHEN ((v_resultats.moyenne >= ( SELECT moyenne_admission.moyenne_admission
               FROM public.moyenne_admission
              ORDER BY moyenne_admission.id_moyenne_admission DESC
             LIMIT 1)) AND ((v_resultats.nombre_ue_validees)::double precision >= v_resultats.nombre_ue_a_valider) AND (v_resultats.nombre_note_eliminatoire = 0)) THEN 'admis'::character varying
            ELSE 'repechage'::character varying
        END AS decision
   FROM public.v_resultats;


ALTER TABLE public.v_decision OWNER TO postgres;

--
-- Name: v_export_ent; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_export_ent AS
 SELECT DISTINCT ON (res.id_au, res.im) res.id_au,
    cm.id_mention_ent AS idmention,
    COALESCE(cp.id_parcours_ent, (0)::bigint) AS idparcours,
    0 AS rang,
        CASE
            WHEN ((res.nombre_ue_validees < res.nombre_ue_a_valider) OR (res.nombre_note_elim > 0)) THEN 'NE'::character varying
            ELSE (round((res.moyenne)::numeric, 2))::character varying
        END AS notefin,
    res.id_niveau AS idniveau,
    res.im
   FROM ((((public.resultats_definitifs res
     JOIN public.parcours p ON ((res.id_parcours = p.id_parcours)))
     JOIN public.mention m ON ((p.id_mention = m.id_mention)))
     LEFT JOIN public.correspondance_parcours_ent cp ON ((p.id_parcours = cp.id_parcours)))
     JOIN public.correspondance_mention_ent cm ON ((m.id_mention = cm.id_mention)))
  ORDER BY res.id_au, res.im;


ALTER TABLE public.v_export_ent OWNER TO postgres;

--
-- Name: v_historique_redoublement_triplement; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_historique_redoublement_triplement AS
 SELECT DISTINCT ON (resultats_definitifs.id_etudiants, resultats_definitifs.id_au) resultats_definitifs.id_au,
    resultats_definitifs.intitule,
    resultats_definitifs.id_parcours,
    resultats_definitifs.nom_parcours,
    resultats_definitifs.id_niveau,
    resultats_definitifs.nom_niveau,
    resultats_definitifs.id_etudiants,
    resultats_definitifs.im,
    resultats_definitifs.nom_etudiant,
    resultats_definitifs.prenoms,
    resultats_definitifs.statut_au_suivante
   FROM public.resultats_definitifs
  WHERE ((((resultats_definitifs.statut_au_suivante)::text = 'redoublant'::text) OR ((resultats_definitifs.statut_au_suivante)::text = 'triplant'::text)) AND (resultats_definitifs.date_annulation_inscription IS NULL))
  ORDER BY resultats_definitifs.id_etudiants, resultats_definitifs.id_au, resultats_definitifs.id_ue;


ALTER TABLE public.v_historique_redoublement_triplement OWNER TO postgres;

--
-- Name: v_resultats_avant_repechage_complet; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.v_resultats_avant_repechage_complet AS
 SELECT r.id_resultats_avant_repechage,
    r.id_note_eval,
    au.id_au,
    au.intitule,
    p.id_parcours,
    p.nom_parcours,
    n.id_niveau,
    n.nom_niveau,
    r.id_examen_par_au,
    r.id_session_examen,
    r.nom_session_examen,
    r.type_session,
    r.date_annulation_inscription,
    r.coefficient,
    ue.id_unite_enseignement,
    ue.nom_unite_enseignement,
    r.id_ue_ec,
    ec.id_element_constitutif,
    ec.nom_element_constitutif,
    e.id_etudiants,
    e.im,
    e.nom,
    e.prenoms,
    r.note_ec,
    r.note_ue,
    r.valide,
    r.total,
    r.total_coefficient,
    r.moyenne,
    r.nombre_ue,
    r.nombre_ue_validees,
    r.nombre_ue_a_valider,
    r.nombre_note_eliminatoire,
    r.decision
   FROM ((((((public.resultats_avant_repechage r
     JOIN public.au ON ((r.id_au = au.id_au)))
     JOIN public.parcours p ON ((r.id_parcours = p.id_parcours)))
     JOIN public.niveau n ON ((r.id_niveau = n.id_niveau)))
     JOIN public.unite_enseignement ue ON ((r.id_unite_enseignement = ue.id_unite_enseignement)))
     JOIN public.element_constitutif ec ON ((r.id_element_constitutif = ec.id_element_constitutif)))
     LEFT JOIN public.etudiants e ON ((r.id_etudiants = e.id_etudiants)))
  WITH NO DATA;


ALTER TABLE public.v_resultats_avant_repechage_complet OWNER TO postgres;

--
-- Name: v_liste_repechage; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.v_liste_repechage AS
 SELECT DISTINCT ON (v_resultats_avant_repechage_complet.id_au, v_resultats_avant_repechage_complet.id_parcours, v_resultats_avant_repechage_complet.id_niveau, v_resultats_avant_repechage_complet.id_etudiants, v_resultats_avant_repechage_complet.id_unite_enseignement) v_resultats_avant_repechage_complet.id_au,
    v_resultats_avant_repechage_complet.intitule,
    v_resultats_avant_repechage_complet.id_parcours,
    v_resultats_avant_repechage_complet.nom_parcours,
    v_resultats_avant_repechage_complet.id_niveau,
    v_resultats_avant_repechage_complet.nom_niveau,
    v_resultats_avant_repechage_complet.id_examen_par_au,
    v_resultats_avant_repechage_complet.id_session_examen,
    v_resultats_avant_repechage_complet.nom_session_examen,
    v_resultats_avant_repechage_complet.type_session,
    v_resultats_avant_repechage_complet.id_unite_enseignement,
    v_resultats_avant_repechage_complet.nom_unite_enseignement,
    v_resultats_avant_repechage_complet.id_etudiants,
    v_resultats_avant_repechage_complet.im,
    v_resultats_avant_repechage_complet.nom,
    v_resultats_avant_repechage_complet.prenoms,
    v_resultats_avant_repechage_complet.valide,
    v_resultats_avant_repechage_complet.decision
   FROM public.v_resultats_avant_repechage_complet
  WHERE ((v_resultats_avant_repechage_complet.decision)::text = 'repechage'::text)
  ORDER BY v_resultats_avant_repechage_complet.id_au, v_resultats_avant_repechage_complet.id_parcours, v_resultats_avant_repechage_complet.id_niveau, v_resultats_avant_repechage_complet.id_etudiants, v_resultats_avant_repechage_complet.id_unite_enseignement, v_resultats_avant_repechage_complet.id_ue_ec
  WITH NO DATA;


ALTER TABLE public.v_liste_repechage OWNER TO postgres;

--
-- Name: v_liste_repechage_affichage; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.v_liste_repechage_affichage AS
 SELECT row_number() OVER (ORDER BY NULL::text) AS "n°",
    v_liste_repechage.im,
    v_liste_repechage.nom,
    v_liste_repechage.prenoms,
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Anglais'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Anglais",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Biologie cellulaire et tissus'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Biologie cellulaire et tissus",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Ethique-Deonthologie-Méthodologie de la recherche'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Ethique-Deonthologie-Méthodologie de la recherche",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Français médical'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Français médical",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Gestion'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Gestion",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Mathématiques-Biophysique-Statistiques'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Mathématiques-Biophysique-Statistiques",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Médecine Légale'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Médecine Légale",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Médecine opératoire'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Médecine opératoire",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Pédiatrie'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Pédiatrie",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Physiologie'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Physiologie",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Réanimation médicale'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Réanimation médicale",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Santé publique'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Santé publique",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Structure et fonction des biomolécules'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Structure et fonction des biomolécules",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Thérapeutique chirurgicale'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Thérapeutique chirurgicale",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Thérapeutique médicale'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Thérapeutique médicale",
    max((
        CASE
            WHEN ((v_liste_repechage.nom_unite_enseignement)::text = 'Urologie'::text) THEN v_liste_repechage.valide
            ELSE NULL::character varying
        END)::text) AS "Résultats Urologie"
   FROM public.v_liste_repechage
  WHERE ((v_liste_repechage.id_au = 5) AND (v_liste_repechage.id_parcours = 4) AND (v_liste_repechage.id_niveau = 2))
  GROUP BY v_liste_repechage.im, v_liste_repechage.nom, v_liste_repechage.prenoms
  WITH NO DATA;


ALTER TABLE public.v_liste_repechage_affichage OWNER TO postgres;

--
-- Name: v_liste_ue_ec; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_liste_ue_ec AS
 SELECT ue.id_unite_enseignement,
    ue.nom_unite_enseignement,
    ec.id_element_constitutif,
    ec.nom_element_constitutif,
    c.coefficient,
    c.id_ue_ec,
    epa.id_examen_par_au,
    se.id_session_examen,
    se.nom_session_examen,
    p.id_parcours,
    p.nom_parcours,
    n.id_niveau,
    n.nom_niveau,
    au.id_au,
    au.intitule
   FROM (((((((public.ue_ec_parcours_niveau_au c
     JOIN public.unite_enseignement ue ON ((c.id_unite_enseignement = ue.id_unite_enseignement)))
     JOIN public.element_constitutif ec ON ((c.id_element_constitutif = ec.id_element_constitutif)))
     JOIN public.examen_par_au epa ON ((c.id_examen_par_au = epa.id_examen_par_au)))
     JOIN public.session_examen se ON ((epa.id_session_examen = se.id_session_examen)))
     JOIN public.parcours p ON ((c.id_parcours = p.id_parcours)))
     JOIN public.niveau n ON ((c.id_niveau = n.id_niveau)))
     JOIN public.au ON ((c.id_au = au.id_au)));


ALTER TABLE public.v_liste_ue_ec OWNER TO postgres;

--
-- Name: v_liste_ue_ec_avec_mentions; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_liste_ue_ec_avec_mentions AS
 SELECT ue.id_unite_enseignement,
    ue.nom_unite_enseignement,
    ec.id_element_constitutif,
    ec.nom_element_constitutif,
    c.coefficient,
    c.id_ue_ec,
    epa.id_examen_par_au,
    se.id_session_examen,
    se.nom_session_examen,
    m.id_mention,
    m.nom_mention,
    p.id_parcours,
    p.nom_parcours,
    n.id_niveau,
    n.nom_niveau,
    au.id_au,
    au.intitule
   FROM ((((((((public.ue_ec_parcours_niveau_au c
     JOIN public.unite_enseignement ue ON ((c.id_unite_enseignement = ue.id_unite_enseignement)))
     JOIN public.element_constitutif ec ON ((c.id_element_constitutif = ec.id_element_constitutif)))
     JOIN public.examen_par_au epa ON ((c.id_examen_par_au = epa.id_examen_par_au)))
     JOIN public.session_examen se ON ((epa.id_session_examen = se.id_session_examen)))
     JOIN public.parcours p ON ((c.id_parcours = p.id_parcours)))
     JOIN public.mention m ON ((p.id_mention = m.id_mention)))
     JOIN public.niveau n ON ((c.id_niveau = n.id_niveau)))
     JOIN public.au ON ((c.id_au = au.id_au)));


ALTER TABLE public.v_liste_ue_ec_avec_mentions OWNER TO postgres;

--
-- Name: v_nbr_etu_par_au_parcours_niveau; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_nbr_etu_par_au_parcours_niveau AS
 SELECT apn.id_au,
    apn.id_parcours,
    apn.id_niveau,
    count(i.id_inscription) AS nbr_inscrits
   FROM ((public.inscription i
     JOIN public.etudiants e ON ((i.id_etudiant = e.id_etudiants)))
     RIGHT JOIN public.v_cj_au_parcours_niveau apn ON (((i.id_au = apn.id_au) AND (i.id_niveau = apn.id_niveau) AND (e.id_parcours = apn.id_parcours))))
  GROUP BY apn.id_au, apn.id_parcours, apn.id_niveau;


ALTER TABLE public.v_nbr_etu_par_au_parcours_niveau OWNER TO postgres;

--
-- Name: v_liste_ue_ec_avec_nbr_inscrits; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.v_liste_ue_ec_avec_nbr_inscrits AS
 SELECT l.id_unite_enseignement,
    l.nom_unite_enseignement,
    l.id_element_constitutif,
    l.nom_element_constitutif,
    l.coefficient,
    l.id_ue_ec,
    l.id_examen_par_au,
    l.id_session_examen,
    l.nom_session_examen,
    l.id_mention,
    l.nom_mention,
    l.id_parcours,
    l.nom_parcours,
    l.id_niveau,
    l.nom_niveau,
    l.id_au,
    l.intitule,
    n.nbr_inscrits
   FROM (public.v_liste_ue_ec_avec_mentions l
     JOIN public.v_nbr_etu_par_au_parcours_niveau n ON (((l.id_parcours = n.id_parcours) AND (l.id_niveau = n.id_niveau) AND (l.id_au = n.id_au))))
  WITH NO DATA;


ALTER TABLE public.v_liste_ue_ec_avec_nbr_inscrits OWNER TO postgres;

--
-- Name: v_non_admis; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_non_admis AS
 SELECT resultats_definitifs.id_resultat_definitif,
    resultats_definitifs.id_au,
    resultats_definitifs.id_parcours,
    resultats_definitifs.id_niveau,
    resultats_definitifs.cycle,
    resultats_definitifs.nom_niveau,
    resultats_definitifs.rang,
    resultats_definitifs.nom_niveau_long,
    resultats_definitifs.id_examen_par_au,
    resultats_definitifs.id_session_examen,
    resultats_definitifs.nom_session_examen,
    resultats_definitifs.type_session_retenue,
    resultats_definitifs.id_etudiants,
    resultats_definitifs.im,
    resultats_definitifs.date_annulation_inscription,
    resultats_definitifs.statut,
    resultats_definitifs.id_ue,
    resultats_definitifs.coef,
    resultats_definitifs.id_ec,
    resultats_definitifs.note_ue,
    resultats_definitifs.note_ec,
    resultats_definitifs.valide,
    resultats_definitifs.total,
    resultats_definitifs.total_coefficient,
    resultats_definitifs.moyenne,
    resultats_definitifs.moyenne_passage,
    resultats_definitifs.nombre_ue,
    resultats_definitifs.nombre_ue_a_valider,
    resultats_definitifs.nombre_ue_validees,
    resultats_definitifs.nombre_note_elim,
    resultats_definitifs.statut_au_suivante,
    resultats_definitifs.id_niveau_suivant,
    resultats_definitifs.intitule,
    resultats_definitifs.nom_parcours,
    resultats_definitifs.nom_niveau_suivant,
    resultats_definitifs.rang_suivant,
    resultats_definitifs.cycle_suivant,
    resultats_definitifs.nom_etudiant,
    resultats_definitifs.prenoms,
    resultats_definitifs.date_naissance,
    resultats_definitifs.lieu_naissance,
    resultats_definitifs.nom_unite_enseignement,
    resultats_definitifs.nom_element_constitutif,
    resultats_definitifs.a_passe_examen,
    resultats_definitifs.created_at,
    resultats_definitifs.updated_at
   FROM public.resultats_definitifs
  WHERE (((resultats_definitifs.statut_au_suivante)::text <> 'passant'::text) AND (resultats_definitifs.id_etudiants IS NOT NULL) AND (resultats_definitifs.date_annulation_inscription IS NULL));


ALTER TABLE public.v_non_admis OWNER TO postgres;

--
-- Name: v_note_eval_complet; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_note_eval_complet AS
 SELECT note.id_note_eval,
    au.id_au,
    au.intitule,
    p.id_parcours,
    p.nom_parcours,
    n.id_niveau,
    n.nom_niveau,
    note.id_examen_par_au,
    note.id_session_examen,
    note.nom_session_examen,
    note.type_session,
    note.date_annulation_inscription,
    note.coefficient,
    ue.id_unite_enseignement,
    ue.nom_unite_enseignement,
    note.id_ue_ec,
    ec.id_element_constitutif,
    ec.nom_element_constitutif,
    e.im,
    e.id_etudiants,
    e.nom,
    e.prenoms,
    note.note_ec,
    note.note_ue,
    note.valide
   FROM ((((((public.note_eval note
     JOIN public.au ON ((note.id_au = au.id_au)))
     JOIN public.parcours p ON ((note.id_parcours = p.id_parcours)))
     JOIN public.niveau n ON ((note.id_niveau = n.id_niveau)))
     JOIN public.unite_enseignement ue ON ((note.id_unite_enseignement = ue.id_unite_enseignement)))
     JOIN public.element_constitutif ec ON ((note.id_element_constitutif = ec.id_element_constitutif)))
     LEFT JOIN public.etudiants e ON ((note.id_etudiants = e.id_etudiants)));


ALTER TABLE public.v_note_eval_complet OWNER TO postgres;

--
-- Name: v_note_eval_ue_complet; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_note_eval_ue_complet AS
 SELECT v.id_note_eval,
    au.id_au,
    au.intitule,
    p.id_parcours,
    p.nom_parcours,
    n.id_niveau,
    n.nom_niveau,
    v.id_examen_par_au,
    v.id_session_examen,
    v.nom_session_examen,
    v.type_session,
    v.date_annulation_inscription,
    v.coefficient,
    ue.id_unite_enseignement,
    ue.nom_unite_enseignement,
    v.im,
    e.id_etudiants,
    e.nom,
    e.prenoms,
    v.note_ue,
    v.valide
   FROM (((((public.v_note_eval_ue v
     JOIN public.au ON ((v.id_au = au.id_au)))
     JOIN public.parcours p ON ((v.id_parcours = p.id_parcours)))
     JOIN public.niveau n ON ((v.id_niveau = n.id_niveau)))
     JOIN public.unite_enseignement ue ON ((v.id_unite_enseignement = ue.id_unite_enseignement)))
     LEFT JOIN public.etudiants e ON ((v.id_etudiants = e.id_etudiants)));


ALTER TABLE public.v_note_eval_ue_complet OWNER TO postgres;

--
-- Name: v_note_moyenne_ue; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_note_moyenne_ue AS
 SELECT v_note.id_au,
    v_note.id_parcours,
    v_note.id_niveau,
    v_note.coefficient,
    v_note.id_unite_enseignement,
    v_note.id_examen_par_au,
    v_note.id_session_examen,
    v_note.im,
    v_note.id_etudiants,
    avg(v_note.note) AS note,
    v_note.date_annulation
   FROM public.v_note
  WHERE ((v_note.type_session)::text = 'eval'::text)
  GROUP BY v_note.id_au, v_note.id_parcours, v_note.id_niveau, v_note.coefficient, v_note.id_unite_enseignement, v_note.id_examen_par_au, v_note.id_session_examen, v_note.im, v_note.id_etudiants, v_note.date_annulation;


ALTER TABLE public.v_note_moyenne_ue OWNER TO postgres;

--
-- Name: v_note_validation_ue; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_note_validation_ue AS
 SELECT v_note_moyenne_ue.id_au,
    v_note_moyenne_ue.id_parcours,
    v_note_moyenne_ue.id_niveau,
    v_note_moyenne_ue.id_examen_par_au,
    v_note_moyenne_ue.id_session_examen,
    v_note_moyenne_ue.coefficient,
    v_note_moyenne_ue.id_unite_enseignement,
    v_note_moyenne_ue.date_annulation,
    v_note_moyenne_ue.im,
    v_note_moyenne_ue.id_etudiants,
    v_note_moyenne_ue.note,
        CASE
            WHEN (v_note_moyenne_ue.note >= ( SELECT note_validation_ue.note_validation_ue AS moyenne
               FROM public.note_validation_ue
              ORDER BY note_validation_ue.id_note_validation_ue DESC
             LIMIT 1)) THEN 'V'::text
            WHEN ((v_note_moyenne_ue.note > (( SELECT note_eliminatoire.note_elim
               FROM public.note_eliminatoire
              ORDER BY note_eliminatoire.id_note_eliminatoire DESC
             LIMIT 1))::double precision) AND (v_note_moyenne_ue.note < ( SELECT note_validation_ue.note_validation_ue
               FROM public.note_validation_ue
              ORDER BY note_validation_ue.id_note_validation_ue DESC
             LIMIT 1))) THEN 'N'::text
            ELSE 'E'::text
        END AS valide
   FROM public.v_note_moyenne_ue;


ALTER TABLE public.v_note_validation_ue OWNER TO postgres;

--
-- Name: v_note_validation_ue_avec_ec; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_note_validation_ue_avec_ec AS
 SELECT n.id_au,
    n.id_parcours,
    n.id_niveau,
    n.id_examen_par_au,
    n.id_session_examen,
    n.nom_session_examen,
    n.type_session,
    n.date_annulation,
    n.coefficient,
    n.id_unite_enseignement,
    n.id_ue_ec,
    n.id_element_constitutif,
    n.im,
    n.id_etudiants,
    n.note AS note_ec,
    v.note AS note_ue,
    v.valide
   FROM (public.v_note_validation_ue v
     JOIN public.v_note n ON ((((n.id_au = v.id_au) AND (n.id_parcours = v.id_parcours) AND (n.id_niveau = v.id_niveau) AND (n.id_unite_enseignement = v.id_unite_enseignement) AND (n.id_examen_par_au = v.id_examen_par_au) AND (n.id_etudiants = v.id_etudiants)) OR ((n.id_au = v.id_au) AND (n.id_parcours = v.id_parcours) AND (n.id_niveau = v.id_niveau) AND (n.id_unite_enseignement = v.id_unite_enseignement) AND (n.id_examen_par_au = v.id_examen_par_au) AND (n.id_etudiants IS NULL) AND (v.id_etudiants IS NULL)))));


ALTER TABLE public.v_note_validation_ue_avec_ec OWNER TO postgres;

--
-- Name: v_operation_par_examen_par_au; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_operation_par_examen_par_au AS
 SELECT o.id_operation_par_examen,
    o.date_ouverture_saisie_note,
    o.id_user_date_ouverture_saisie_note,
    o.date_cloture_saisie_note,
    o.id_user_date_cloture_saisie_note,
    o.date_ouverture_verification_note,
    o.id_user_date_ouverture_verification_note,
    o.date_cloture_verification_note,
    o.id_user_date_cloture_verification_note,
    o.date_ouverture_saisie_en_tete,
    o.id_user_date_ouverture_saisie_en_tete,
    o.date_cloture_saisie_en_tete,
    o.id_user_date_cloture_saisie_en_tete,
    o.date_ouverture_verification_en_tete,
    o.id_user_date_ouverture_verification_en_tete,
    o.date_cloture_verification_en_tete,
    o.id_user_date_cloture_verification_en_tete,
    o.date_resultats,
    o.id_user_date_resultats,
    o.id_examen_par_au,
    o.created_at,
    o.updated_at,
    e.id_session_examen,
    e.id_au
   FROM (public.operation_par_examen o
     JOIN public.examen_par_au e ON ((o.id_examen_par_au = e.id_examen_par_au)));


ALTER TABLE public.v_operation_par_examen_par_au OWNER TO postgres;

--
-- Name: v_parcours_niveau; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_parcours_niveau AS
 SELECT p.id_parcours,
    p.nom_parcours,
    n.id_niveau,
    n.nom_niveau,
    n.rang
   FROM ((public.parcours p
     JOIN public.parcours_niveau pn ON ((pn.id_parcours = p.id_parcours)))
     JOIN public.niveau n ON ((pn.id_niveau = n.id_niveau)));


ALTER TABLE public.v_parcours_niveau OWNER TO postgres;

--
-- Name: v_resultats_avant_deliberation; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.v_resultats_avant_deliberation AS
 SELECT ra.id_resultat_avant_deliberation,
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
    ns.cycle AS cycle_suivant
   FROM (((((((public.resultats_avant_deliberation ra
     JOIN public.au ON ((ra.id_au = au.id_au)))
     JOIN public.parcours p ON ((ra.id_parcours = p.id_parcours)))
     JOIN public.niveau n ON ((ra.id_niveau = n.id_niveau)))
     JOIN public.etudiants e ON ((ra.id_etudiants = e.id_etudiants)))
     JOIN public.unite_enseignement ue ON ((ra.id_unite_enseignement = ue.id_unite_enseignement)))
     JOIN public.element_constitutif ec ON ((ra.id_element_constitutif = ec.id_element_constitutif)))
     LEFT JOIN public.niveau ns ON ((ra.id_niveau_suivant = ns.id_niveau)))
  WITH NO DATA;


ALTER TABLE public.v_resultats_avant_deliberation OWNER TO postgres;

--
-- Name: v_resultats_avec_notes; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_resultats_avec_notes AS
 SELECT n.id_note_eval,
    n.id_au,
    n.id_parcours,
    n.id_niveau,
    n.id_examen_par_au,
    n.id_session_examen,
    n.nom_session_examen,
    n.type_session,
    n.date_annulation_inscription,
    n.coefficient,
    n.id_unite_enseignement,
    n.id_ue_ec,
    n.id_element_constitutif,
    n.im,
    n.id_etudiants,
    n.note_ec,
    n.note_ue,
    n.valide,
    n.created_at,
    n.updated_at,
    d.total,
    d.total_coefficient,
    d.moyenne,
    d.nombre_ue,
    d.nombre_ue_validees,
    d.nombre_ue_a_valider,
    d.nombre_note_eliminatoire,
    d.decision
   FROM (public.v_decision d
     JOIN public.note_eval n ON ((((d.id_au = n.id_au) AND (d.id_parcours = n.id_parcours) AND (d.id_niveau = n.id_niveau) AND (d.id_etudiants = n.id_etudiants)) OR ((d.id_au = n.id_au) AND (d.id_parcours = n.id_parcours) AND (d.id_niveau = n.id_niveau) AND (d.id_etudiants IS NULL) AND (n.id_etudiants IS NULL)))));


ALTER TABLE public.v_resultats_avec_notes OWNER TO postgres;

--
-- Name: v_selectionnes_parcours; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.v_selectionnes_parcours AS
 SELECT s.id_selectionnes,
    s.nom,
    s.prenoms,
    s.num_bacc,
    s.id_parcours,
    s.id_au,
    s.est_inscrit,
    s.created_at,
    s.updated_at,
    s.id_agent,
    p.nom_parcours
   FROM (public.selectionnes s
     JOIN public.parcours p ON ((s.id_parcours = p.id_parcours)));


ALTER TABLE public.v_selectionnes_parcours OWNER TO postgres;

--
-- Name: au id_au; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.au ALTER COLUMN id_au SET DEFAULT nextval('public.au_id_au_seq'::regclass);


--
-- Name: autres_etablissements id_autre_etablissement; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.autres_etablissements ALTER COLUMN id_autre_etablissement SET DEFAULT nextval('public.autres_etablissements_id_autre_etablissement_seq'::regclass);


--
-- Name: correspondance_mention_ent id_correspondance_mention_ent; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.correspondance_mention_ent ALTER COLUMN id_correspondance_mention_ent SET DEFAULT nextval('public.correspondance_mention_ent_id_correspondance_mention_ent_seq'::regclass);


--
-- Name: correspondance_parcours_ent id_correspondance_parcours_ent; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.correspondance_parcours_ent ALTER COLUMN id_correspondance_parcours_ent SET DEFAULT nextval('public.correspondance_parcours_ent_id_correspondance_parcours_ent_seq'::regclass);


--
-- Name: cte_codes_barres_en_plus id_cte_codes_barres_en_plus; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cte_codes_barres_en_plus ALTER COLUMN id_cte_codes_barres_en_plus SET DEFAULT nextval('public.cte_codes_barres_en_plus_id_cte_codes_barres_en_plus_seq'::regclass);


--
-- Name: element_constitutif id_element_constitutif; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.element_constitutif ALTER COLUMN id_element_constitutif SET DEFAULT nextval('public.element_constitutif_id_element_constitutif_seq'::regclass);


--
-- Name: etudiants id_etudiants; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.etudiants ALTER COLUMN id_etudiants SET DEFAULT nextval('public.etudiants_id_etudiants_seq'::regclass);


--
-- Name: examen_par_au id_examen_par_au; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.examen_par_au ALTER COLUMN id_examen_par_au SET DEFAULT nextval('public.examen_par_au_id_examen_par_au_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: inscription id_inscription; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inscription ALTER COLUMN id_inscription SET DEFAULT nextval('public.inscription_id_inscription_seq'::regclass);


--
-- Name: mention id_mention; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mention ALTER COLUMN id_mention SET DEFAULT nextval('public.mention_id_mention_seq'::regclass);


--
-- Name: mention_ent id_mention_ent; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mention_ent ALTER COLUMN id_mention_ent SET DEFAULT nextval('public.mention_ent_id_mention_ent_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: moyenne_admission id_moyenne_admission; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.moyenne_admission ALTER COLUMN id_moyenne_admission SET DEFAULT nextval('public.moyenne_admission_id_moyenne_admission_seq'::regclass);


--
-- Name: nationalites id_nationalites; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.nationalites ALTER COLUMN id_nationalites SET DEFAULT nextval('public.nationalites_id_nationalites_seq'::regclass);


--
-- Name: niveau id_niveau; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.niveau ALTER COLUMN id_niveau SET DEFAULT nextval('public.niveau_id_niveau_seq'::regclass);


--
-- Name: note_eliminatoire id_note_eliminatoire; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eliminatoire ALTER COLUMN id_note_eliminatoire SET DEFAULT nextval('public.note_eliminatoire_id_note_eliminatoire_seq'::regclass);


--
-- Name: note_eval id_note_eval; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eval ALTER COLUMN id_note_eval SET DEFAULT nextval('public.note_eval_id_note_eval_seq'::regclass);


--
-- Name: note_max id_note_max; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_max ALTER COLUMN id_note_max SET DEFAULT nextval('public.note_max_id_note_max_seq'::regclass);


--
-- Name: note_validation_ue id_note_validation_ue; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_validation_ue ALTER COLUMN id_note_validation_ue SET DEFAULT nextval('public.note_validation_ue_id_note_validation_ue_seq'::regclass);


--
-- Name: operation_par_au id_operation_par_au; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_au ALTER COLUMN id_operation_par_au SET DEFAULT nextval('public.operation_par_au_id_operation_par_au_seq'::regclass);


--
-- Name: operation_par_deliberation id_operation_sur_deliberation; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_deliberation ALTER COLUMN id_operation_sur_deliberation SET DEFAULT nextval('public.operation_par_deliberation_id_operation_sur_deliberation_seq'::regclass);


--
-- Name: operation_par_examen id_operation_par_examen; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen ALTER COLUMN id_operation_par_examen SET DEFAULT nextval('public.operation_par_examen_id_operation_par_examen_seq'::regclass);


--
-- Name: operation_par_import_resultat_paces id_operation_par_import_resultat_paces; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_import_resultat_paces ALTER COLUMN id_operation_par_import_resultat_paces SET DEFAULT nextval('public.operation_par_import_resultat_id_operation_par_import_resul_seq'::regclass);


--
-- Name: operation_sur_examen_par_au id_operation_sur_examen_par_au; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_sur_examen_par_au ALTER COLUMN id_operation_sur_examen_par_au SET DEFAULT nextval('public.operation_sur_examen_par_au_id_operation_sur_examen_par_au_seq'::regclass);


--
-- Name: parcours id_parcours; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parcours ALTER COLUMN id_parcours SET DEFAULT nextval('public.parcours_id_parcours_seq'::regclass);


--
-- Name: parcours_ent id_parcours_ent; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parcours_ent ALTER COLUMN id_parcours_ent SET DEFAULT nextval('public.parcours_ent_id_parcours_ent_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: pourcentage_admission id_pourcentage_admission; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pourcentage_admission ALTER COLUMN id_pourcentage_admission SET DEFAULT nextval('public.pourcentage_admission_id_pourcentage_admission_seq'::regclass);


--
-- Name: province id_province; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.province ALTER COLUMN id_province SET DEFAULT nextval('public.province_id_province_seq'::regclass);


--
-- Name: resultats_avant_deliberation id_resultat_avant_deliberation; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_deliberation ALTER COLUMN id_resultat_avant_deliberation SET DEFAULT nextval('public.resultats_avant_deliberation_id_resultat_avant_deliberation_seq'::regclass);


--
-- Name: resultats_avant_repechage id_resultats_avant_repechage; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage ALTER COLUMN id_resultats_avant_repechage SET DEFAULT nextval('public.resultats_avant_repechage_id_resultats_avant_repechage_seq'::regclass);


--
-- Name: resultats_definitifs id_resultat_definitif; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_definitifs ALTER COLUMN id_resultat_definitif SET DEFAULT nextval('public.resultats_definitifs_id_resultat_definitif_seq'::regclass);


--
-- Name: role id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role ALTER COLUMN id SET DEFAULT nextval('public.role_id_seq'::regclass);


--
-- Name: selectionnes id_selectionnes; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.selectionnes ALTER COLUMN id_selectionnes SET DEFAULT nextval('public.selectionnes_id_selectionnes_seq'::regclass);


--
-- Name: serie id_serie; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.serie ALTER COLUMN id_serie SET DEFAULT nextval('public.serie_id_serie_seq'::regclass);


--
-- Name: session_examen id_session_examen; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.session_examen ALTER COLUMN id_session_examen SET DEFAULT nextval('public.session_examen_id_session_examen_seq'::regclass);


--
-- Name: ue_ec_parcours_niveau_au id_ue_ec; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ue_ec_parcours_niveau_au ALTER COLUMN id_ue_ec SET DEFAULT nextval('public.ue_ec_parcours_niveau_au_id_ue_ec_seq'::regclass);


--
-- Name: unite_enseignement id_unite_enseignement; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.unite_enseignement ALTER COLUMN id_unite_enseignement SET DEFAULT nextval('public.unite_enseignement_id_unite_enseignement_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: au; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.au VALUES (1, '2023-2024', '2024-10-10', '2024-10-10', NULL, NULL);
INSERT INTO public.au VALUES (2, '2024-2025', '2024-10-11', '2024-10-11', '2024-10-11 08:56:00', '2024-10-11 09:03:23');
INSERT INTO public.au VALUES (3, '2025-2026', '2024-10-11', '2024-11-09', '2024-10-11 09:03:33', '2024-11-09 08:11:18');
INSERT INTO public.au VALUES (4, '2026-2027', '2024-11-09', '2024-12-03', '2024-11-09 08:11:36', '2024-12-03 08:08:57');
INSERT INTO public.au VALUES (5, '2027-2028', '2024-12-03', '2024-12-13', '2024-12-03 08:09:07', '2024-12-13 12:28:12');
INSERT INTO public.au VALUES (6, '2028-2029', '2024-12-13', NULL, '2024-12-13 12:28:24', '2024-12-13 12:28:24');


--
-- Data for Name: autres_etablissements; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.autres_etablissements VALUES (1, 'Faculté de Médecine d''Antsiranana', NULL, NULL);
INSERT INTO public.autres_etablissements VALUES (2, 'Faculté de Médecine de Mahajanga', NULL, NULL);
INSERT INTO public.autres_etablissements VALUES (3, 'Faculté de Médecine de Toamasina', NULL, NULL);
INSERT INTO public.autres_etablissements VALUES (4, 'Faculté de Médecine de Fianarantsoa', NULL, NULL);
INSERT INTO public.autres_etablissements VALUES (5, 'Faculté de Médecine de Toliara', NULL, NULL);


--
-- Data for Name: autres_inscriptions; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: barcode_matricule; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.barcode_matricule VALUES (218, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (218, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (218, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (231, 1, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (231, 2, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (231, 3, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (232, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (232, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (232, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (230, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (230, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (230, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (236, 1, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (236, 2, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (236, 3, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (240, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (240, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (240, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (226, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (226, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (226, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (223, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (223, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (223, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (222, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (222, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (222, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (238, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (238, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (238, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (228, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (228, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (228, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (251, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (251, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (251, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (250, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (250, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (250, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (246, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (246, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (246, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (245, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (245, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (245, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (242, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (242, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (242, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (257, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (257, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (257, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (256, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (256, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (256, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (258, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (258, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (258, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (248, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (248, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (248, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (261, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (261, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (261, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (260, 1, '40500', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (260, 2, '40501', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (260, 3, '40502', NULL, NULL, NULL);
INSERT INTO public.barcode_matricule VALUES (239, 1, '40500', NULL, NULL, NULL);


--
-- Data for Name: barcode_note; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.barcode_note VALUES (218, 1, 4, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (218, 2, 9.5, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (218, 3, 7, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (228, 1, 13, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (228, 2, 12, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (228, 3, 7, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (238, 1, 8, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (238, 2, 14, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (238, 3, 13.25, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (222, 1, 12, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (222, 2, 19, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (222, 3, 14, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (223, 1, 3, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (223, 2, 2, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (223, 3, 1, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (226, 1, 7, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (226, 2, 6, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (226, 3, 5, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (240, 1, 5, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (240, 2, 8, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (240, 3, 10, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (236, 1, 7, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (236, 2, 6, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (236, 3, 9, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (230, 1, 7, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (230, 2, 8, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (230, 3, 12.5, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (232, 1, 4.5, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (232, 2, 4.3, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (232, 3, 2, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (231, 1, 15, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (231, 3, 14, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (231, 2, 9.75, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (260, 1, 10, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (260, 2, 4, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (260, 3, 3, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (261, 1, 7, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (261, 2, 4, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (261, 3, 2, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (248, 1, 8.5, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (248, 2, 10, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (248, 3, 10.5, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (258, 1, 14, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (258, 2, 8, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (258, 3, 7, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (256, 1, 7, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (256, 2, 18, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (256, 3, 14, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (257, 1, 7.5, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (257, 2, 4.5, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (257, 3, 6.25, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (242, 1, 18, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (242, 2, 14, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (242, 3, 15, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (245, 1, 14, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (245, 2, 12, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (245, 3, 9, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (246, 1, 6.5, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (246, 2, 4.25, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (246, 3, 10, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (250, 1, 3.25, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (250, 2, 10, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (250, 3, 14, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (251, 1, 5, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (251, 2, 6, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (251, 3, 10, NULL, NULL, NULL);
INSERT INTO public.barcode_note VALUES (239, 1, 18, NULL, NULL, NULL);


--
-- Data for Name: correspondance_mention_ent; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.correspondance_mention_ent VALUES (1, 1, 1, NULL, NULL);
INSERT INTO public.correspondance_mention_ent VALUES (2, 2, 3, NULL, NULL);
INSERT INTO public.correspondance_mention_ent VALUES (3, 3, 2, NULL, NULL);
INSERT INTO public.correspondance_mention_ent VALUES (4, 4, 4, NULL, NULL);


--
-- Data for Name: correspondance_parcours_ent; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.correspondance_parcours_ent VALUES (1, 4, 2, NULL, NULL);
INSERT INTO public.correspondance_parcours_ent VALUES (2, 5, 3, NULL, NULL);
INSERT INTO public.correspondance_parcours_ent VALUES (3, 6, 7, NULL, NULL);
INSERT INTO public.correspondance_parcours_ent VALUES (4, 7, 6, NULL, NULL);
INSERT INTO public.correspondance_parcours_ent VALUES (5, 8, 8, NULL, NULL);
INSERT INTO public.correspondance_parcours_ent VALUES (6, 9, 1, NULL, NULL);
INSERT INTO public.correspondance_parcours_ent VALUES (7, 10, 4, NULL, NULL);
INSERT INTO public.correspondance_parcours_ent VALUES (8, 11, 5, NULL, NULL);


--
-- Data for Name: cte_codes_barres_en_plus; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.cte_codes_barres_en_plus VALUES (1, 10, NULL, NULL);
INSERT INTO public.cte_codes_barres_en_plus VALUES (2, 1000, NULL, NULL);
INSERT INTO public.cte_codes_barres_en_plus VALUES (3, 10, NULL, NULL);


--
-- Data for Name: element_constitutif; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.element_constitutif VALUES (1, 'structure prof Raobela', '2024-10-11 15:06:02', '2024-10-11 15:06:02');
INSERT INTO public.element_constitutif VALUES (2, 'strucuture prof willy', '2024-10-11 15:06:16', '2024-10-11 15:06:16');
INSERT INTO public.element_constitutif VALUES (3, 'Biologie cellulaire prof X', '2024-10-11 15:26:22', '2024-10-11 15:26:22');
INSERT INTO public.element_constitutif VALUES (4, 'Biologie cellulaire prof Y', '2024-10-11 15:26:33', '2024-10-11 15:26:33');
INSERT INTO public.element_constitutif VALUES (5, 'Ethique-DeonthologieMéthodologie de la recherche', '2024-10-11 15:27:09', '2024-10-11 15:27:09');
INSERT INTO public.element_constitutif VALUES (6, 'Anatomie prof. P', '2024-10-14 12:02:19', '2024-10-14 12:02:19');
INSERT INTO public.element_constitutif VALUES (7, 'biologie cellulaire et tissus', '2024-10-14 12:41:10', '2024-10-14 12:41:10');
INSERT INTO public.element_constitutif VALUES (8, 'physiologie', '2024-10-14 12:42:12', '2024-10-14 12:42:12');
INSERT INTO public.element_constitutif VALUES (9, 'Mathématiques', '2024-10-14 12:44:38', '2024-10-14 12:44:38');
INSERT INTO public.element_constitutif VALUES (10, 'biophysique', '2024-10-14 12:44:53', '2024-10-14 12:44:53');
INSERT INTO public.element_constitutif VALUES (11, 'Statistiques', '2024-10-14 12:45:03', '2024-10-14 12:45:03');
INSERT INTO public.element_constitutif VALUES (12, 'Biologie cellulaire', '2024-11-06 19:56:23', '2024-11-06 19:56:23');
INSERT INTO public.element_constitutif VALUES (13, 'tissus', '2024-11-06 19:56:27', '2024-11-06 19:56:27');
INSERT INTO public.element_constitutif VALUES (14, 'Gestion', '2024-11-09 08:16:26', '2024-11-09 08:16:26');
INSERT INTO public.element_constitutif VALUES (15, 'Anglais', '2024-11-09 08:17:04', '2024-11-09 08:17:04');
INSERT INTO public.element_constitutif VALUES (16, 'Français Médical', '2024-11-09 08:17:28', '2024-11-09 08:17:28');
INSERT INTO public.element_constitutif VALUES (17, 'Pharmacologie', '2024-11-14 10:52:46', '2024-11-14 10:52:46');
INSERT INTO public.element_constitutif VALUES (18, 'Santé publique', '2024-11-14 11:07:23', '2024-11-14 11:07:23');
INSERT INTO public.element_constitutif VALUES (19, 'Thérapeutique chirurgicale', '2024-11-14 11:09:05', '2024-11-14 11:09:05');
INSERT INTO public.element_constitutif VALUES (20, 'Thérapeurtique chirurgicale EC 2', '2024-11-14 11:09:30', '2024-11-14 11:09:30');
INSERT INTO public.element_constitutif VALUES (21, 'Thérapeutique médicale', '2024-11-14 11:10:12', '2024-11-14 11:10:12');
INSERT INTO public.element_constitutif VALUES (22, 'Médecine opératoire', '2024-11-14 11:10:50', '2024-11-14 11:10:50');
INSERT INTO public.element_constitutif VALUES (23, 'Urologie 1', '2024-11-14 11:12:03', '2024-11-14 11:12:03');
INSERT INTO public.element_constitutif VALUES (24, 'Urologie 2', '2024-11-14 11:12:11', '2024-11-14 11:12:11');
INSERT INTO public.element_constitutif VALUES (25, 'Réanimation médicale EC 1', '2024-11-14 11:13:03', '2024-11-14 11:13:03');
INSERT INTO public.element_constitutif VALUES (26, 'Réanimation Médicale Ec 2', '2024-11-14 11:13:16', '2024-11-14 11:13:16');
INSERT INTO public.element_constitutif VALUES (27, 'Pédiatire', '2024-11-14 11:13:57', '2024-11-14 11:13:57');
INSERT INTO public.element_constitutif VALUES (28, 'Médecine Légale EC 1', '2024-11-14 11:15:11', '2024-11-14 11:15:11');
INSERT INTO public.element_constitutif VALUES (29, 'Médecine Légale EC2', '2024-11-14 11:15:20', '2024-11-14 11:15:20');
INSERT INTO public.element_constitutif VALUES (30, 'UE non spécifique', '2025-01-08 15:26:14', '2025-01-08 15:26:14');


--
-- Data for Name: etudiants; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.etudiants VALUES (18, '40204', 'RAKOTOBE', 'Jean ferlin', 'm', '2024-11-09', '2001-08-25', 'Antsiranana', NULL, NULL, NULL, 'Lot II N 68 AB Besarety', '0000000000', 'RAKOTOBE Pierre', 'Ministre', '0000000000', '000000', 'RAZAIMAMONJY Flore', 'Ambassadrice', '0000000000', '0000', false, 4, '2024-11-09 08:24:30', '2024-11-09 08:24:30', 4, 2024, 1, 1, 1, NULL, NULL, 1, 1, 1, '/var/www/html/scolarite/storage/photo_etudiants/photo_40204.jpeg');
INSERT INTO public.etudiants VALUES (19, '40205', 'ANDRIAMANOHISOA', 'Andiva', 'm', '2024-11-09', '2001-03-21', 'Antananarivo', NULL, NULL, NULL, 'nnnn Faravohitra', '0343373626', 'ANDRIAMANOHISOA Gérard', 'Secrétaire d''Etat', '0124578980', 'ampatsakana', 'ANDRIAMANOHISOA Florine', 'Enseignante', '0000000000', 'Ampatsakana', false, 4, '2024-11-09 08:26:54', '2024-11-09 08:26:54', 4, 2024, 1, 1, 1, NULL, NULL, 1, 1, 1, '/var/www/html/scolarite/storage/photo_etudiants/photo_40205.jpeg');
INSERT INTO public.etudiants VALUES (20, '40206', 'ANDRIANAVALONA', 'Setra', 'm', '2024-11-09', '1999-08-04', 'Antsiranana', NULL, NULL, NULL, '56 bis Antanetibe', '0000000000', 'ANDRIANAVALONA Setra Pere', 'Médecin', '0000000000', 'Masay', 'RAZANAMANGA Claudine', 'Fermière', '0000000000', 'Ampatsakana', false, 4, '2024-11-09 08:29:48', '2024-11-09 08:29:48', 4, 2024, 1, 1, 1, NULL, NULL, 1, 1, 1, '/var/www/html/scolarite/storage/photo_etudiants/photo_40206.jpeg');
INSERT INTO public.etudiants VALUES (17, '40200', 'RAKOTONDRAINIBE', 'Jean François', 'm', '2024-11-07', '2000-08-05', 'Besarety, Antananarivo', NULL, NULL, NULL, 'Lot II N 68 AB Besarety', '0000000000', 'RAKOTO Pierre', 'Boucher', '0000000000', 'Lot II N 68 AB Besarety', 'Randy Fanja', 'Bouchère', '0000000000', 'Lot II N 68 AB Besarety', false, 4, '2024-11-07 07:46:32', '2024-11-07 07:46:32', 4, 2024, 1, 1, 1, NULL, NULL, 2, 1, 1, '/var/www/html/scolarite/storage/photo_etudiants/photo_40200.jpeg');
INSERT INTO public.etudiants VALUES (24, '40500', 'ANDRIAMANOHISOA', 'Andiva', 'm', '2024-12-03', '2001-03-21', 'Avaradoha', NULL, NULL, NULL, 'nnnn Faravohitra', '0343373626', 'RAKOTO Pierre', 'Médecin', '0000000000', 'Masay', 'RANDIMBIARIVONY Fanjaniaina', 'Bouchère', '0000000000', 'Masay', false, 4, '2024-12-03 08:42:29', '2024-12-03 08:42:29', 3, 2027, 1, 1, 1, NULL, 'renato@gmail', 1, 4, 1, '/var/www/html/scolarite/storage/photo_etudiants/photo_40500.jpeg');
INSERT INTO public.etudiants VALUES (25, '40501', 'JEAN BE', 'Pierre', 'm', '2024-12-03', '2001-08-22', 'Antsiranana', NULL, NULL, NULL, 'nnnn Faravohitra', '1234567890', 'RAKOTO Pierre', 'Boucher', '0000000000', 'Masay', 'Razay', 'Bouchère', '0000000000', 'Masay', false, 4, '2024-12-03 08:44:46', '2024-12-03 08:44:46', 3, 2027, 1, 1, 1, NULL, 'adjoint@mail.com', 1, 4, 1, '/var/www/html/scolarite/storage/photo_etudiants/photo_40501.jpeg');
INSERT INTO public.etudiants VALUES (26, '40502', 'RAKOTOBE', 'Paul', 'm', '2024-12-03', '2004-09-19', 'Amboasary', NULL, NULL, NULL, 'adr', '0000000000', 'Rakoto Pierre', 'Boucher', '0000000000', 'Lot II N 68 AB Besarety', 'RANDIMBIARIVONY Fanjaniaina', 'Bouchère', '0340918289', 'e', false, 4, '2024-12-03 08:46:19', '2024-12-03 08:46:19', 3, 2027, 1, 1, 1, NULL, 'renato@gmail', 1, 4, 1, '/var/www/html/scolarite/storage/photo_etudiants/photo_40502.jpeg');


--
-- Data for Name: examen_par_au; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.examen_par_au VALUES (1, 1, 2, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (2, 2, 2, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (3, 1, 3, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (4, 2, 3, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (5, 1, 4, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (6, 2, 4, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (7, 3, 4, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (8, 10, 4, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (9, 1, 5, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (10, 2, 5, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (11, 3, 5, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (12, 10, 5, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (13, 1, 6, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (14, 2, 6, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (15, 3, 6, NULL, NULL);
INSERT INTO public.examen_par_au VALUES (16, 10, 6, NULL, NULL);


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: inscription; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.inscription VALUES (17, '2024-11-07', NULL, 4, NULL, 17, 2, 3, '2024-11-07 07:46:32', '2024-11-07 07:46:32', NULL, 'passant', NULL);
INSERT INTO public.inscription VALUES (18, '2024-11-09', NULL, 4, NULL, 18, 2, 4, '2024-11-09 08:24:30', '2024-11-09 08:24:30', NULL, 'passant', NULL);
INSERT INTO public.inscription VALUES (19, '2024-11-09', NULL, 4, NULL, 19, 2, 4, '2024-11-09 08:26:54', '2024-11-09 08:26:54', NULL, 'passant', NULL);
INSERT INTO public.inscription VALUES (20, '2024-11-09', NULL, 4, NULL, 20, 2, 4, '2024-11-09 08:29:48', '2024-11-09 08:29:48', NULL, 'passant', NULL);
INSERT INTO public.inscription VALUES (24, '2024-12-03', NULL, 3, NULL, 24, 2, 5, '2024-12-03 08:42:29', '2024-12-03 08:42:29', NULL, 'passant', NULL);
INSERT INTO public.inscription VALUES (25, '2024-12-03', NULL, 3, NULL, 25, 2, 5, '2024-12-03 08:44:46', '2024-12-03 08:44:46', NULL, 'passant', NULL);
INSERT INTO public.inscription VALUES (26, '2024-12-03', NULL, 3, NULL, 26, 2, 5, '2024-12-03 08:46:19', '2024-12-03 08:46:19', NULL, 'passant', NULL);
INSERT INTO public.inscription VALUES (28, '2024-12-13', NULL, 3, NULL, 25, 2, 6, NULL, NULL, NULL, 'redoublant', NULL);
INSERT INTO public.inscription VALUES (29, '2024-12-13', NULL, 3, NULL, 26, 3, 6, NULL, NULL, NULL, 'passant', NULL);
INSERT INTO public.inscription VALUES (27, '2024-12-13', NULL, 3, NULL, 24, 2, 6, NULL, '2024-12-13 13:38:01', '2024-12-13', 'redoublant', NULL);


--
-- Data for Name: mention; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.mention VALUES (1, 'Médecine Humaine', NULL, NULL);
INSERT INTO public.mention VALUES (2, 'Pharmacie', NULL, NULL);
INSERT INTO public.mention VALUES (3, 'Médecine Vétérinaire', NULL, NULL);
INSERT INTO public.mention VALUES (4, 'Sciences Paramédicales', NULL, NULL);


--
-- Data for Name: mention_ent; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.mention_ent VALUES (1, 'Médecine Humaine', NULL, NULL);
INSERT INTO public.mention_ent VALUES (2, 'Médecine Vétérinaire', NULL, NULL);
INSERT INTO public.mention_ent VALUES (3, 'Pharmacie', NULL, NULL);
INSERT INTO public.mention_ent VALUES (4, 'Sciences Paramédicales', NULL, NULL);
INSERT INTO public.mention_ent VALUES (5, 'Médecine Humaine', NULL, NULL);
INSERT INTO public.mention_ent VALUES (6, 'Médecine Vétérinaire', NULL, NULL);
INSERT INTO public.mention_ent VALUES (7, 'Pharmacie', NULL, NULL);
INSERT INTO public.mention_ent VALUES (8, 'Sciences Paramédicales', NULL, NULL);


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.migrations VALUES (1, '2014_10_12_000000_create_users_table', 1);
INSERT INTO public.migrations VALUES (2, '2014_10_12_100000_create_password_reset_tokens_table', 1);
INSERT INTO public.migrations VALUES (3, '2019_08_19_000000_create_failed_jobs_table', 1);
INSERT INTO public.migrations VALUES (4, '2019_12_14_000001_create_personal_access_tokens_table', 1);
INSERT INTO public.migrations VALUES (5, '2024_08_13_064800_create_role_table', 1);
INSERT INTO public.migrations VALUES (6, '2024_08_13_065623_ajout_fk_users', 1);
INSERT INTO public.migrations VALUES (7, '2024_08_27_075437_add_date_suppr_column_to_users', 1);
INSERT INTO public.migrations VALUES (8, '2024_08_27_082822_create_view_users_valides', 1);
INSERT INTO public.migrations VALUES (9, '2024_09_05_082627_au_creation', 1);
INSERT INTO public.migrations VALUES (10, '2024_09_06_052727_au_cloture_to_nullable', 1);
INSERT INTO public.migrations VALUES (11, '2024_09_06_060226_au_intitule_to_unique', 1);
INSERT INTO public.migrations VALUES (12, '2024_09_09_065045_create_mention', 1);
INSERT INTO public.migrations VALUES (13, '2024_09_09_065054_create_parcours', 1);
INSERT INTO public.migrations VALUES (14, '2024_09_09_070126_creation_selectionnes', 1);
INSERT INTO public.migrations VALUES (15, '2024_09_10_110851_prenom_selectionnes_to_nullable', 1);
INSERT INTO public.migrations VALUES (16, '2024_09_10_124828_pk_selectionnes', 1);
INSERT INTO public.migrations VALUES (17, '2024_09_11_082821_selectionnes_ajout_agent', 1);
INSERT INTO public.migrations VALUES (18, '2024_09_11_103716_agent_to_fk', 1);
INSERT INTO public.migrations VALUES (19, '2024_09_12_105247_create_view_sel_parc', 1);
INSERT INTO public.migrations VALUES (20, '2024_09_13_065131_create_etudiants', 1);
INSERT INTO public.migrations VALUES (21, '2024_09_13_114832_create_nationalites_table', 1);
INSERT INTO public.migrations VALUES (22, '2024_09_16_060419_create_series_table', 1);
INSERT INTO public.migrations VALUES (23, '2024_09_16_060430_create_provinces_table', 1);
INSERT INTO public.migrations VALUES (24, '2024_09_16_084106_etu_fk', 1);
INSERT INTO public.migrations VALUES (25, '2024_09_16_113531_etudiant_ajout_type_pi', 1);
INSERT INTO public.migrations VALUES (26, '2024_09_16_123258_autres_inscriptions', 1);
INSERT INTO public.migrations VALUES (27, '2024_09_17_052740_create_inscriptions_table', 1);
INSERT INTO public.migrations VALUES (28, '2024_09_18_072303_v_inscrits', 1);
INSERT INTO public.migrations VALUES (29, '2024_09_20_110712_ajout_date_certificat_scol_inscription', 1);
INSERT INTO public.migrations VALUES (30, '2024_09_20_110903_alter_v_inscrits', 1);
INSERT INTO public.migrations VALUES (31, '2024_09_23_115151_au_ajout_niv_long', 1);
INSERT INTO public.migrations VALUES (32, '2024_09_23_115346_v_inscrits', 1);
INSERT INTO public.migrations VALUES (33, '2024_09_25_085422_etudiants_ajout_email', 1);
INSERT INTO public.migrations VALUES (34, '2024_09_26_145704_creat_parcours_niveau', 1);
INSERT INTO public.migrations VALUES (35, '2024_09_26_170219_create_v_parcours_niveau', 1);
INSERT INTO public.migrations VALUES (36, '2024_09_27_055356_create_autres_etablissements', 1);
INSERT INTO public.migrations VALUES (37, '2024_09_27_055435_transferts_autorises', 1);
INSERT INTO public.migrations VALUES (38, '2024_09_27_085350_etudiants_ajout_id_au_transfert', 1);
INSERT INTO public.migrations VALUES (39, '2024_09_27_090156_etudiants_ajout_id_niveau_transfert', 1);
INSERT INTO public.migrations VALUES (40, '2024_10_03_060437_create_element_constitutifs_table', 1);
INSERT INTO public.migrations VALUES (41, '2024_10_03_060442_create_unite_enseignements_table', 1);
INSERT INTO public.migrations VALUES (42, '2024_10_06_143956_create_session_examen_centre', 1);
INSERT INTO public.migrations VALUES (43, '2024_10_08_130238_ue_ec_unique_constraint', 1);
INSERT INTO public.migrations VALUES (44, '2024_10_10_083853_role_ajout_rang', 1);
INSERT INTO public.migrations VALUES (123, '2024_11_02_081519_ajout_photo_etudiants;', 12);
INSERT INTO public.migrations VALUES (124, '2024_11_04_191441_creation_v_liste_ue_ec_avec_nbr_inscrits_v_materialisee', 12);
INSERT INTO public.migrations VALUES (125, '2024_11_05_061300_creation_index_barcode_note_matricule', 12);
INSERT INTO public.migrations VALUES (48, '2024_10_11_081315_create_view_v_liste_ue_ec_avec_mentions', 2);
INSERT INTO public.migrations VALUES (49, '2024_10_11_095050_creation_v_nbr_etu_par_au_parcours_niveau', 3);
INSERT INTO public.migrations VALUES (50, '2024_10_11_102352_creation_v_liste_ue_ec_avec_nbr_inscrits', 3);
INSERT INTO public.migrations VALUES (51, '2024_10_11_141643_create_cte_codes_barres_en_plus', 4);
INSERT INTO public.migrations VALUES (126, '2024_11_05_152833_unique_index_examen_par_au', 12);
INSERT INTO public.migrations VALUES (127, '2024_11_06_090040_creation_v_correspondance_note_matricule', 12);
INSERT INTO public.migrations VALUES (128, '2024_11_06_115747_creation_index_id_etudiants_inscription', 12);
INSERT INTO public.migrations VALUES (55, '2024_10_16_071533_create_operation_sur_examen', 5);
INSERT INTO public.migrations VALUES (56, '2024_10_19_074350_creation_v_operation_sur_examen_par_au', 6);
INSERT INTO public.migrations VALUES (57, '2024_10_22_134008_create_note_max', 7);
INSERT INTO public.migrations VALUES (58, '2024_10_22_175353_create_barcode_note', 8);
INSERT INTO public.migrations VALUES (380, '2024_11_06_120000_inscription_ajout_statut', 13);
INSERT INTO public.migrations VALUES (60, '2024_10_26_100420_barcode_note_ajout_verifie', 9);
INSERT INTO public.migrations VALUES (61, '2024_10_28_150509_crete_barcode_matricule', 10);
INSERT INTO public.migrations VALUES (381, '2024_11_06_120001_inscription_ajout_a_passe_examen', 13);
INSERT INTO public.migrations VALUES (382, '2024_11_06_121117_creation_v_inscrits2', 13);
INSERT INTO public.migrations VALUES (383, '2024_11_06_123247_creatio_index_etu_inscr_ue_ec', 13);
INSERT INTO public.migrations VALUES (384, '2024_11_06_152730_ajout_type_session_examen', 13);
INSERT INTO public.migrations VALUES (385, '2024_11_06_153818_delete_nullable_type_session_examen', 14);
INSERT INTO public.migrations VALUES (386, '2024_11_06_163624_creation_v_association_etu_ec', 14);
INSERT INTO public.migrations VALUES (387, '2024_11_06_164024_creation_v_note', 14);
INSERT INTO public.migrations VALUES (388, '2024_11_06_192814_creation_v_note_moyenne_ue', 14);
INSERT INTO public.migrations VALUES (389, '2024_11_07_084524_create_note_eliminatoire', 14);
INSERT INTO public.migrations VALUES (390, '2024_11_07_085000_create_note_validation_ue', 14);
INSERT INTO public.migrations VALUES (391, '2024_11_07_085806_v_note_validation_ue', 14);
INSERT INTO public.migrations VALUES (392, '2024_11_07_100337_v_note_validation_ue_avec_ec', 14);
INSERT INTO public.migrations VALUES (393, '2024_11_07_113229_create_note_eval', 14);
INSERT INTO public.migrations VALUES (394, '2024_11_07_145838_creation_v_note_eval_ue', 14);
INSERT INTO public.migrations VALUES (395, '2024_11_08_072355_creation_v_note_eval_ue_complet', 14);
INSERT INTO public.migrations VALUES (396, '2024_11_08_075456_creation_creer_v_resultats_eval', 14);
INSERT INTO public.migrations VALUES (397, '2024_11_11_204214_creation_v_total', 14);
INSERT INTO public.migrations VALUES (398, '2024_11_12_055014_creation_v_liste_ue', 14);
INSERT INTO public.migrations VALUES (399, '2024_11_12_075032_creation_v_nombre_ue_valiees', 14);
INSERT INTO public.migrations VALUES (400, '2024_11_12_083430_creation_v_nombre_ue_validees_sans_inscrits', 14);
INSERT INTO public.migrations VALUES (401, '2024_11_12_084759_creation_v_nombre_ue_validees_complet', 14);
INSERT INTO public.migrations VALUES (523, '2024_11_25_074155_creation_resultats_definitifs', 19);
INSERT INTO public.migrations VALUES (524, '2024_12_02_073411_creation_fonction_statuer', 19);
INSERT INTO public.migrations VALUES (525, '2024_12_03_061652_statut_niveau_suivant', 19);
INSERT INTO public.migrations VALUES (526, '2024_12_04_061717_create_resultats_avant_deliberation', 19);
INSERT INTO public.migrations VALUES (527, '2024_12_04_063936_v_calcul_resultats_avant_deliberation', 19);
INSERT INTO public.migrations VALUES (528, '2024_12_04_111806_v_resultats_avant_deliberation', 19);
INSERT INTO public.migrations VALUES (533, '2024_12_09_060025_creation_pk_operation_par_deliberation', 20);
INSERT INTO public.migrations VALUES (534, '2024_12_10_061723_v_deliberations', 21);
INSERT INTO public.migrations VALUES (536, '2024_12_19_133404_create_mention_ent', 23);
INSERT INTO public.migrations VALUES (537, '2024_12_19_133422_create_parcours_ent', 23);
INSERT INTO public.migrations VALUES (538, '2024_12_19_133521_create_correspondance_mention_ent', 23);
INSERT INTO public.migrations VALUES (539, '2024_12_19_133532_create_correspondance_parcours_ent', 23);
INSERT INTO public.migrations VALUES (122, '2024_10_29_051712_create_v_check_inscription_ue_ec', 11);
INSERT INTO public.migrations VALUES (535, '2024_12_10_111042_resultats_definitifs_ajout_a_ete_delibere', 22);
INSERT INTO public.migrations VALUES (543, '2024_12_20_073226_creation_v_export_ent', 24);
INSERT INTO public.migrations VALUES (544, '2025_01_08_134940_create_operation_par_import_resultat_paces', 25);
INSERT INTO public.migrations VALUES (440, '2024_11_12_090055_creation_v_nombre_ue', 15);
INSERT INTO public.migrations VALUES (441, '2024_11_12_103717_creation_v_nombre_note_eliminatoire', 15);
INSERT INTO public.migrations VALUES (442, '2024_11_12_112244_creation_v_moyenne', 15);
INSERT INTO public.migrations VALUES (443, '2024_11_13_051756_creation_v_note_eval_complet', 15);
INSERT INTO public.migrations VALUES (444, '2024_11_13_103003_creation_moyenne_validation', 15);
INSERT INTO public.migrations VALUES (445, '2024_11_13_103511_pourcentage_admission', 15);
INSERT INTO public.migrations VALUES (446, '2024_11_13_104000_creation_v_nombre_ue_a_valider', 15);
INSERT INTO public.migrations VALUES (447, '2024_11_13_104846_v_resultats', 16);
INSERT INTO public.migrations VALUES (448, '2024_11_13_183257_creation_v_decision', 16);
INSERT INTO public.migrations VALUES (449, '2024_11_13_185814_creation_v_resultats_avaec_notes', 17);
INSERT INTO public.migrations VALUES (450, '2024_11_14_044958_create_resultats_avant_repechage', 17);
INSERT INTO public.migrations VALUES (451, '2024_11_14_055326_create_operation_par_au', 17);
INSERT INTO public.migrations VALUES (452, '2024_11_14_134152_creation_v_resultats_avant_repechage_complet', 17);
INSERT INTO public.migrations VALUES (453, '2024_11_15_110407_v_liste_repechage', 17);
INSERT INTO public.migrations VALUES (454, '2024_11_15_124807_creation_fonction_creer_v_liste_repechage_affichage', 17);
INSERT INTO public.migrations VALUES (455, '2024_11_18_132213_modification_v_note_moyenne_ue', 17);
INSERT INTO public.migrations VALUES (456, '2024_11_18_135708_inscription_statut_delet_nullable', 17);
INSERT INTO public.migrations VALUES (457, '2024_11_19_062429_creation_v_note_moyenne_ue_rep', 17);
INSERT INTO public.migrations VALUES (458, '2024_11_19_064419_v_note_ue_avec_ec_rep', 17);
INSERT INTO public.migrations VALUES (459, '2024_11_19_094513_creation_v_assemblage_eval_repe', 17);
INSERT INTO public.migrations VALUES (460, '2024_11_19_105756_modification_v_liste_ec', 17);
INSERT INTO public.migrations VALUES (461, '2024_11_19_114129_creation_v_note_ue_rep', 17);
INSERT INTO public.migrations VALUES (462, '2024_11_19_120656_creation_v_total_note_rep', 17);
INSERT INTO public.migrations VALUES (463, '2024_11_19_122343_creation_v_moyenne_rep', 17);
INSERT INTO public.migrations VALUES (464, '2024_11_19_124536_v_validation_ue_rep', 17);
INSERT INTO public.migrations VALUES (465, '2024_11_20_065059_comptage_ue_validees_rep', 17);
INSERT INTO public.migrations VALUES (466, '2024_11_20_083441_creation_v_nombre_note_eliminatoire_rep', 17);
INSERT INTO public.migrations VALUES (467, '2024_11_20_120915_creation_v_conditions_passage', 17);
INSERT INTO public.migrations VALUES (468, '2024_11_25_072800_niveau_ajout_cycle', 17);
INSERT INTO public.migrations VALUES (476, '2024_11_25_073403_niveau_cycle_to_nullable', 18);


--
-- Data for Name: moyenne_admission; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.moyenne_admission VALUES (1, 10, NULL, NULL);


--
-- Data for Name: nationalites; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.nationalites VALUES (1, 'Malgache', NULL, NULL);
INSERT INTO public.nationalites VALUES (2, 'Comorien', NULL, NULL);
INSERT INTO public.nationalites VALUES (3, 'Camerounais', NULL, NULL);
INSERT INTO public.nationalites VALUES (4, 'Egyptien', NULL, NULL);
INSERT INTO public.nationalites VALUES (5, 'Lybanais', NULL, NULL);
INSERT INTO public.nationalites VALUES (6, 'Autre', NULL, NULL);


--
-- Data for Name: niveau; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.niveau VALUES (1, 'PACES', 1, NULL, NULL, 'Première Année Commune des Etudes de Santé', 1);
INSERT INTO public.niveau VALUES (2, 'NIVEAU L2', 2, NULL, NULL, NULL, 1);
INSERT INTO public.niveau VALUES (3, 'NIVEAU L3', 3, NULL, NULL, NULL, 1);
INSERT INTO public.niveau VALUES (4, 'QUATRIEME année', 4, NULL, NULL, NULL, 2);
INSERT INTO public.niveau VALUES (5, 'CINQUIEME année', 5, NULL, NULL, NULL, 2);
INSERT INTO public.niveau VALUES (6, 'SIXIEME année', 6, NULL, NULL, NULL, 2);


--
-- Data for Name: note_eliminatoire; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.note_eliminatoire VALUES (1, 5.00, NULL, NULL);
INSERT INTO public.note_eliminatoire VALUES (2, 5.00, NULL, NULL);


--
-- Data for Name: note_eval; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.note_eval VALUES (242, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 257, 26, '40500', 24, 7.5, 7.25, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (243, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 256, 25, '40500', 24, 7, 7.25, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (244, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 13, 245, 19, '40500', 24, 14, 14, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (245, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 261, 29, '40500', 24, 7, 8.5, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (246, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 260, 28, '40500', 24, 10, 8.5, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (247, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 18, 258, 27, '40500', 24, 14, 14, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (248, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 251, 24, '40500', 24, 5, 4.125, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (249, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 250, 23, '40500', 24, 3.25, 4.125, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (250, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 15, 248, 22, '40500', 24, 8.5, 8.5, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (251, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 14, 246, 21, '40500', 24, 6.5, 6.5, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (252, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 12, 242, 18, '40500', 24, 18, 18, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (253, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 257, 26, '40501', 25, 4.5, 11.25, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (254, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 256, 25, '40501', 25, 18, 11.25, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (255, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 13, 245, 19, '40501', 25, 12, 12, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (256, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 261, 29, '40501', 25, 4, 4, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (257, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 260, 28, '40501', 25, 4, 4, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (258, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 18, 258, 27, '40501', 25, 8, 8, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (259, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 251, 24, '40501', 25, 6, 8, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (260, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 250, 23, '40501', 25, 10, 8, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (261, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 15, 248, 22, '40501', 25, 10, 10, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (262, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 14, 246, 21, '40501', 25, 4.25, 4.25, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (263, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 12, 242, 18, '40501', 25, 14, 14, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (264, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 257, 26, '40502', 26, 6.25, 10.125, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (265, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 256, 25, '40502', 26, 14, 10.125, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (266, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 13, 245, 19, '40502', 26, 9, 9, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (267, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 261, 29, '40502', 26, 2, 2.5, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (268, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 260, 28, '40502', 26, 3, 2.5, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (269, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 18, 258, 27, '40502', 26, 7, 7, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (270, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 251, 24, '40502', 26, 10, 12, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (271, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 250, 23, '40502', 26, 14, 12, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (272, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 15, 248, 22, '40502', 26, 10.5, 10.5, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (273, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 14, 246, 21, '40502', 26, 10, 10, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (274, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 12, 242, 18, '40502', 26, 15, 15, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (206, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 20, 226, 5, '40500', 24, 7, 7, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (207, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 10, 240, 16, '40500', 24, 5, 5, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (208, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 9, 238, 15, '40500', 24, 8, 8, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (209, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 8, 236, 14, '40500', 24, 9, 9, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (210, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 232, 9, '40500', 24, 4.5, 8.5, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (211, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 231, 11, '40500', 24, 14, 8.5, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (212, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 230, 10, '40500', 24, 7, 8.5, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (213, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 6, 228, 8, '40500', 24, 13, 13, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (214, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 223, 4, '40500', 24, 3, 7.5, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (215, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 222, 3, '40500', 24, 12, 7.5, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (216, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 219, 2, '40500', 24, 0, 2, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (217, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 218, 1, '40500', 24, 4, 2, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (218, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 20, 226, 5, '40501', 25, 6, 6, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (219, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 10, 240, 16, '40501', 25, 8, 8, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (220, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 9, 238, 15, '40501', 25, 14, 14, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (221, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 8, 236, 14, '40501', 25, 7, 7, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (222, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 232, 9, '40501', 25, 4.3, 9.1, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (223, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 231, 11, '40501', 25, 15, 9.1, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (224, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 230, 10, '40501', 25, 8, 9.1, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (225, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 6, 228, 8, '40501', 25, 12, 12, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (226, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 223, 4, '40501', 25, 2, 10.5, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (227, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 222, 3, '40501', 25, 19, 10.5, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (228, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 219, 2, '40501', 25, 0, 4.75, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (229, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 218, 1, '40501', 25, 9.5, 4.75, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (230, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 20, 226, 5, '40502', 26, 5, 5, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (231, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 10, 240, 16, '40502', 26, 10, 10, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (232, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 9, 238, 15, '40502', 26, 13.25, 13.25, 'V', NULL, NULL);
INSERT INTO public.note_eval VALUES (233, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 8, 236, 14, '40502', 26, 6, 6, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (234, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 232, 9, '40502', 26, 2, 8.083333333333334, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (235, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 231, 11, '40502', 26, 9.75, 8.083333333333334, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (236, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 230, 10, '40502', 26, 12.5, 8.083333333333334, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (237, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 6, 228, 8, '40502', 26, 7, 7, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (238, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 223, 4, '40502', 26, 1, 7.5, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (239, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 222, 3, '40502', 26, 14, 7.5, 'N', NULL, NULL);
INSERT INTO public.note_eval VALUES (240, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 219, 2, '40502', 26, 0, 3.5, 'E', NULL, NULL);
INSERT INTO public.note_eval VALUES (241, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 218, 1, '40502', 26, 7, 3.5, 'E', NULL, NULL);


--
-- Data for Name: note_max; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.note_max VALUES (1, 20, NULL, NULL);
INSERT INTO public.note_max VALUES (2, 20, NULL, NULL);
INSERT INTO public.note_max VALUES (3, 20, NULL, NULL);
INSERT INTO public.note_max VALUES (4, 20, NULL, NULL);


--
-- Data for Name: note_validation_ue; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.note_validation_ue VALUES (1, 10, NULL, NULL);
INSERT INTO public.note_validation_ue VALUES (2, 10, NULL, NULL);


--
-- Data for Name: operation_par_au; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.operation_par_au VALUES (1, 5, '2024-12-03', NULL, NULL, NULL, '2024-12-06', NULL, '2024-12-12', 5, NULL, NULL);


--
-- Data for Name: operation_par_deliberation; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.operation_par_deliberation VALUES (6, 5, 4, 2, '2024-12-09', '2024-12-10', 5, NULL);


--
-- Data for Name: operation_par_examen; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.operation_par_examen VALUES (13, '2024-12-04', NULL, '2024-12-04', NULL, '2024-12-04', NULL, '2024-12-04', NULL, '2024-12-04', NULL, '2024-12-04', NULL, '2024-12-04', NULL, '2024-12-04', NULL, '2024-12-06', NULL, 11, NULL, NULL);
INSERT INTO public.operation_par_examen VALUES (9, '2024-11-19', NULL, '2024-11-19', NULL, '2024-11-19', NULL, '2024-11-19', NULL, '2024-12-04', NULL, '2024-11-19', NULL, '2024-11-19', NULL, '2024-11-19', NULL, NULL, NULL, 5, NULL, NULL);
INSERT INTO public.operation_par_examen VALUES (10, '2024-11-19', NULL, '2024-11-19', NULL, '2024-11-19', NULL, '2024-11-19', NULL, '2024-12-04', NULL, '2024-11-19', NULL, '2024-11-19', NULL, '2024-11-19', NULL, NULL, NULL, 6, NULL, NULL);
INSERT INTO public.operation_par_examen VALUES (11, '2024-12-03', NULL, '2024-12-03', NULL, '2024-12-03', NULL, '2024-12-03', NULL, '2024-12-04', NULL, '2024-12-03', NULL, '2024-12-03', NULL, '2024-12-03', NULL, '2024-12-03', NULL, 9, NULL, NULL);
INSERT INTO public.operation_par_examen VALUES (12, '2024-12-03', NULL, '2024-12-03', NULL, '2024-12-03', NULL, '2024-12-03', NULL, '2024-12-04', NULL, '2024-12-03', NULL, '2024-12-03', NULL, '2024-12-03', NULL, '2024-12-03', NULL, 10, NULL, NULL);


--
-- Data for Name: operation_par_import_resultat_paces; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: operation_sur_examen_par_au; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: parcours; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.parcours VALUES (1, 'Médecine Humaine', 1, NULL, NULL);
INSERT INTO public.parcours VALUES (2, 'Pharmacie', 2, NULL, NULL);
INSERT INTO public.parcours VALUES (3, 'Médecine Vétérinaire', 3, NULL, NULL);
INSERT INTO public.parcours VALUES (4, 'Anesthésie', 4, NULL, NULL);
INSERT INTO public.parcours VALUES (5, 'Electroradiologie', 4, NULL, NULL);
INSERT INTO public.parcours VALUES (6, 'Ergothérapie', 4, NULL, NULL);
INSERT INTO public.parcours VALUES (7, 'Maïeutique', 4, NULL, NULL);
INSERT INTO public.parcours VALUES (8, 'Massokinésithérapie', 4, NULL, NULL);
INSERT INTO public.parcours VALUES (9, 'Sciences Infirmières', 4, NULL, NULL);
INSERT INTO public.parcours VALUES (10, 'Technique d''Appareillage et Orthopédie', 4, NULL, NULL);
INSERT INTO public.parcours VALUES (11, 'Technique de Laboratoire', 4, NULL, NULL);


--
-- Data for Name: parcours_ent; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.parcours_ent VALUES (1, 'Sciences Infirmières', NULL, NULL);
INSERT INTO public.parcours_ent VALUES (2, 'Anesthésie', NULL, NULL);
INSERT INTO public.parcours_ent VALUES (3, 'Electroradiologie', NULL, NULL);
INSERT INTO public.parcours_ent VALUES (4, 'Technique d''Appareillage et Orthopédie', NULL, NULL);
INSERT INTO public.parcours_ent VALUES (5, 'Technique de Laboratoire', NULL, NULL);
INSERT INTO public.parcours_ent VALUES (6, 'Maïeutique', NULL, NULL);
INSERT INTO public.parcours_ent VALUES (7, 'Ergothérapie', NULL, NULL);
INSERT INTO public.parcours_ent VALUES (8, 'Massokinésithérapie', NULL, NULL);


--
-- Data for Name: parcours_niveau; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.parcours_niveau VALUES (1, 1);
INSERT INTO public.parcours_niveau VALUES (2, 1);
INSERT INTO public.parcours_niveau VALUES (3, 1);
INSERT INTO public.parcours_niveau VALUES (4, 1);
INSERT INTO public.parcours_niveau VALUES (5, 1);
INSERT INTO public.parcours_niveau VALUES (6, 1);
INSERT INTO public.parcours_niveau VALUES (1, 2);
INSERT INTO public.parcours_niveau VALUES (2, 2);
INSERT INTO public.parcours_niveau VALUES (3, 2);
INSERT INTO public.parcours_niveau VALUES (4, 2);
INSERT INTO public.parcours_niveau VALUES (5, 2);
INSERT INTO public.parcours_niveau VALUES (6, 2);
INSERT INTO public.parcours_niveau VALUES (1, 3);
INSERT INTO public.parcours_niveau VALUES (2, 3);
INSERT INTO public.parcours_niveau VALUES (3, 3);
INSERT INTO public.parcours_niveau VALUES (4, 3);
INSERT INTO public.parcours_niveau VALUES (5, 3);
INSERT INTO public.parcours_niveau VALUES (6, 3);
INSERT INTO public.parcours_niveau VALUES (1, 4);
INSERT INTO public.parcours_niveau VALUES (2, 4);
INSERT INTO public.parcours_niveau VALUES (3, 4);
INSERT INTO public.parcours_niveau VALUES (1, 5);
INSERT INTO public.parcours_niveau VALUES (2, 5);
INSERT INTO public.parcours_niveau VALUES (3, 5);
INSERT INTO public.parcours_niveau VALUES (1, 6);
INSERT INTO public.parcours_niveau VALUES (2, 6);
INSERT INTO public.parcours_niveau VALUES (3, 6);
INSERT INTO public.parcours_niveau VALUES (1, 7);
INSERT INTO public.parcours_niveau VALUES (2, 7);
INSERT INTO public.parcours_niveau VALUES (3, 7);
INSERT INTO public.parcours_niveau VALUES (1, 8);
INSERT INTO public.parcours_niveau VALUES (2, 8);
INSERT INTO public.parcours_niveau VALUES (3, 8);
INSERT INTO public.parcours_niveau VALUES (1, 9);
INSERT INTO public.parcours_niveau VALUES (2, 9);
INSERT INTO public.parcours_niveau VALUES (3, 9);
INSERT INTO public.parcours_niveau VALUES (1, 10);
INSERT INTO public.parcours_niveau VALUES (2, 10);
INSERT INTO public.parcours_niveau VALUES (3, 10);
INSERT INTO public.parcours_niveau VALUES (1, 11);
INSERT INTO public.parcours_niveau VALUES (2, 11);
INSERT INTO public.parcours_niveau VALUES (3, 11);


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--



--
-- Data for Name: pourcentage_admission; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.pourcentage_admission VALUES (1, 75, NULL, NULL);


--
-- Data for Name: province; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.province VALUES (1, 'Antananarivo', NULL, NULL);
INSERT INTO public.province VALUES (2, 'Antsiranana', NULL, NULL);
INSERT INTO public.province VALUES (3, 'Toamasina', NULL, NULL);
INSERT INTO public.province VALUES (4, 'Toliara', NULL, NULL);
INSERT INTO public.province VALUES (5, 'Fianarantsoa', NULL, NULL);
INSERT INTO public.province VALUES (6, 'Mahajanga', NULL, NULL);
INSERT INTO public.province VALUES (7, 'Etranger', NULL, NULL);


--
-- Data for Name: resultats_avant_deliberation; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.resultats_avant_deliberation VALUES (139, 5, 4, 2, 24, 9, 1, 1, 2, 'Evaluation 1', 'eval', 1, '40500', 2, 0, 'E', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (140, 5, 4, 2, 24, 9, 1, 1, 1, 'Evaluation 1', 'eval', 1, '40500', 2, 4, 'E', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (141, 5, 4, 2, 25, 9, 1, 1, 2, 'Evaluation 1', 'eval', 1, '40501', 4.75, 0, 'E', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (142, 5, 4, 2, 25, 9, 1, 1, 1, 'Evaluation 1', 'eval', 1, '40501', 4.75, 9.5, 'E', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (143, 5, 4, 2, 26, 9, 1, 1, 2, 'Evaluation 1', 'eval', 1, '40502', 3.5, 0, 'E', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (144, 5, 4, 2, 26, 9, 1, 1, 1, 'Evaluation 1', 'eval', 1, '40502', 3.5, 7, 'E', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (145, 5, 4, 2, 24, 9, 1, 5, 4, 'Evaluation 1', 'eval', 1, '40500', 7.5, 3, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (146, 5, 4, 2, 24, 9, 1, 5, 3, 'Evaluation 1', 'eval', 1, '40500', 7.5, 12, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (147, 5, 4, 2, 25, 9, 1, 5, 4, 'Evaluation 1', 'eval', 1, '40501', 10.5, 2, 'V', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (148, 5, 4, 2, 25, 9, 1, 5, 3, 'Evaluation 1', 'eval', 1, '40501', 10.5, 19, 'V', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (149, 5, 4, 2, 26, 9, 1, 5, 4, 'Evaluation 1', 'eval', 1, '40502', 7.5, 1, 'N', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (150, 5, 4, 2, 26, 9, 1, 5, 3, 'Evaluation 1', 'eval', 1, '40502', 7.5, 14, 'N', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (151, 5, 4, 2, 24, 9, 1, 6, 8, 'Evaluation 1', 'eval', 1, '40500', 13, 13, 'V', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (152, 5, 4, 2, 25, 9, 1, 6, 8, 'Evaluation 1', 'eval', 1, '40501', 12, 12, 'V', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (153, 5, 4, 2, 26, 9, 1, 6, 8, 'Evaluation 1', 'eval', 1, '40502', 7, 7, 'N', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (154, 5, 4, 2, 24, 9, 1, 7, 9, 'Evaluation 1', 'eval', 1, '40500', 8.5, 4.5, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (155, 5, 4, 2, 24, 9, 1, 7, 11, 'Evaluation 1', 'eval', 1, '40500', 8.5, 14, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (156, 5, 4, 2, 24, 9, 1, 7, 10, 'Evaluation 1', 'eval', 1, '40500', 8.5, 7, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (157, 5, 4, 2, 25, 9, 1, 7, 9, 'Evaluation 1', 'eval', 1, '40501', 9.1, 4.3, 'N', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (158, 5, 4, 2, 25, 9, 1, 7, 11, 'Evaluation 1', 'eval', 1, '40501', 9.1, 15, 'N', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (159, 5, 4, 2, 25, 9, 1, 7, 10, 'Evaluation 1', 'eval', 1, '40501', 9.1, 8, 'N', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (160, 5, 4, 2, 26, 9, 1, 7, 9, 'Evaluation 1', 'eval', 1, '40502', 8.083333333333334, 2, 'N', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (161, 5, 4, 2, 26, 9, 1, 7, 11, 'Evaluation 1', 'eval', 1, '40502', 8.083333333333334, 9.75, 'N', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (162, 5, 4, 2, 26, 9, 1, 7, 10, 'Evaluation 1', 'eval', 1, '40502', 8.083333333333334, 12.5, 'N', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (163, 5, 4, 2, 24, 9, 1, 8, 14, 'Evaluation 1', 'eval', 1, '40500', 9, 9, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (164, 5, 4, 2, 25, 9, 1, 8, 14, 'Evaluation 1', 'eval', 1, '40501', 7, 7, 'N', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (165, 5, 4, 2, 26, 9, 1, 8, 14, 'Evaluation 1', 'eval', 1, '40502', 6, 6, 'N', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (166, 5, 4, 2, 24, 9, 1, 9, 15, 'Evaluation 1', 'repe', 1, '40500', 18, 18, 'V', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (167, 5, 4, 2, 25, 9, 1, 9, 15, 'Evaluation 1', 'eval', 1, '40501', 14, 14, 'V', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (168, 5, 4, 2, 26, 9, 1, 9, 15, 'Evaluation 1', 'eval', 1, '40502', 13.25, 13.25, 'V', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (169, 5, 4, 2, 24, 9, 1, 10, 16, 'Evaluation 1', 'eval', 1, '40500', 5, 5, 'E', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (170, 5, 4, 2, 25, 9, 1, 10, 16, 'Evaluation 1', 'eval', 1, '40501', 8, 8, 'N', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (171, 5, 4, 2, 26, 9, 1, 10, 16, 'Evaluation 1', 'eval', 1, '40502', 10, 10, 'V', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (172, 5, 4, 2, 24, 9, 1, 20, 5, 'Evaluation 1', 'eval', 1, '40500', 7, 7, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (173, 5, 4, 2, 25, 9, 1, 20, 5, 'Evaluation 1', 'eval', 1, '40501', 6, 6, 'N', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (174, 5, 4, 2, 26, 9, 1, 20, 5, 'Evaluation 1', 'eval', 1, '40502', 5, 5, 'E', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (175, 5, 4, 2, 24, 10, 2, 12, 18, 'Evaluation 2', 'eval', 1, '40500', 18, 18, 'V', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (176, 5, 4, 2, 25, 10, 2, 12, 18, 'Evaluation 2', 'eval', 1, '40501', 14, 14, 'V', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (177, 5, 4, 2, 26, 10, 2, 12, 18, 'Evaluation 2', 'eval', 1, '40502', 15, 15, 'V', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (178, 5, 4, 2, 24, 10, 2, 13, 19, 'Evaluation 2', 'eval', 1, '40500', 14, 14, 'V', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (179, 5, 4, 2, 25, 10, 2, 13, 19, 'Evaluation 2', 'eval', 1, '40501', 12, 12, 'V', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (180, 5, 4, 2, 26, 10, 2, 13, 19, 'Evaluation 2', 'eval', 1, '40502', 9, 9, 'N', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (181, 5, 4, 2, 24, 10, 2, 14, 21, 'Evaluation 2', 'eval', 1, '40500', 6.5, 6.5, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (182, 5, 4, 2, 25, 10, 2, 14, 21, 'Evaluation 2', 'eval', 1, '40501', 4.25, 4.25, 'E', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (183, 5, 4, 2, 26, 10, 2, 14, 21, 'Evaluation 2', 'eval', 1, '40502', 10, 10, 'V', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (184, 5, 4, 2, 24, 10, 2, 15, 22, 'Evaluation 2', 'eval', 1, '40500', 8.5, 8.5, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (185, 5, 4, 2, 25, 10, 2, 15, 22, 'Evaluation 2', 'eval', 1, '40501', 10, 10, 'V', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (186, 5, 4, 2, 26, 10, 2, 15, 22, 'Evaluation 2', 'eval', 1, '40502', 10.5, 10.5, 'V', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (187, 5, 4, 2, 24, 10, 2, 16, 24, 'Evaluation 2', 'eval', 1, '40500', 4.125, 5, 'E', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (188, 5, 4, 2, 24, 10, 2, 16, 23, 'Evaluation 2', 'eval', 1, '40500', 4.125, 3.25, 'E', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (189, 5, 4, 2, 25, 10, 2, 16, 24, 'Evaluation 2', 'eval', 1, '40501', 8, 6, 'N', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (190, 5, 4, 2, 25, 10, 2, 16, 23, 'Evaluation 2', 'eval', 1, '40501', 8, 10, 'N', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (191, 5, 4, 2, 26, 10, 2, 16, 24, 'Evaluation 2', 'eval', 1, '40502', 12, 10, 'V', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (192, 5, 4, 2, 26, 10, 2, 16, 23, 'Evaluation 2', 'eval', 1, '40502', 12, 14, 'V', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (193, 5, 4, 2, 24, 10, 2, 17, 26, 'Evaluation 2', 'eval', 1, '40500', 7.25, 7.5, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (194, 5, 4, 2, 24, 10, 2, 17, 25, 'Evaluation 2', 'eval', 1, '40500', 7.25, 7, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (195, 5, 4, 2, 25, 10, 2, 17, 26, 'Evaluation 2', 'eval', 1, '40501', 11.25, 4.5, 'V', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (196, 5, 4, 2, 25, 10, 2, 17, 25, 'Evaluation 2', 'eval', 1, '40501', 11.25, 18, 'V', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (197, 5, 4, 2, 26, 10, 2, 17, 26, 'Evaluation 2', 'eval', 1, '40502', 10.125, 6.25, 'V', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (198, 5, 4, 2, 26, 10, 2, 17, 25, 'Evaluation 2', 'eval', 1, '40502', 10.125, 14, 'V', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (199, 5, 4, 2, 24, 10, 2, 18, 27, 'Evaluation 2', 'eval', 1, '40500', 14, 14, 'V', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (200, 5, 4, 2, 25, 10, 2, 18, 27, 'Evaluation 2', 'eval', 1, '40501', 8, 8, 'N', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (201, 5, 4, 2, 26, 10, 2, 18, 27, 'Evaluation 2', 'eval', 1, '40502', 7, 7, 'N', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (202, 5, 4, 2, 24, 10, 2, 19, 29, 'Evaluation 2', 'eval', 1, '40500', 8.5, 7, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (203, 5, 4, 2, 24, 10, 2, 19, 28, 'Evaluation 2', 'eval', 1, '40500', 8.5, 10, 'N', 'passant', NULL, NULL, 150.875, 16, 10, 9.4296875, 16, 12, 5, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (204, 5, 4, 2, 25, 10, 2, 19, 29, 'Evaluation 2', 'eval', 1, '40501', 4, 4, 'E', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (205, 5, 4, 2, 25, 10, 2, 19, 28, 'Evaluation 2', 'eval', 1, '40501', 4, 4, 'E', 'passant', NULL, NULL, 142.85, 16, 10, 8.928125, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (206, 5, 4, 2, 26, 10, 2, 19, 29, 'Evaluation 2', 'eval', 1, '40502', 2.5, 2, 'E', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);
INSERT INTO public.resultats_avant_deliberation VALUES (207, 5, 4, 2, 26, 10, 2, 19, 28, 'Evaluation 2', 'eval', 1, '40502', 2.5, 3, 'E', 'passant', NULL, NULL, 136.45833333333334, 16, 10, 8.528645833333334, 16, 12, 7, 3, 'redoublant', 2, NULL, NULL);


--
-- Data for Name: resultats_avant_repechage; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.resultats_avant_repechage VALUES (1, 242, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 257, 26, '40500', 24, 7.5, 7.25, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (2, 243, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 256, 25, '40500', 24, 7, 7.25, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (3, 244, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 13, 245, 19, '40500', 24, 14, 14, 'V', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (4, 245, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 261, 29, '40500', 24, 7, 8.5, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (5, 246, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 260, 28, '40500', 24, 10, 8.5, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (6, 247, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 18, 258, 27, '40500', 24, 14, 14, 'V', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (7, 248, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 251, 24, '40500', 24, 5, 4.125, 'E', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (8, 249, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 250, 23, '40500', 24, 3.25, 4.125, 'E', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (9, 250, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 15, 248, 22, '40500', 24, 8.5, 8.5, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (10, 251, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 14, 246, 21, '40500', 24, 6.5, 6.5, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (11, 252, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 12, 242, 18, '40500', 24, 18, 18, 'V', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (12, 206, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 20, 226, 5, '40500', 24, 7, 7, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (13, 207, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 10, 240, 16, '40500', 24, 5, 5, 'E', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (14, 208, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 9, 238, 15, '40500', 24, 8, 8, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (15, 209, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 8, 236, 14, '40500', 24, 9, 9, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (16, 210, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 232, 9, '40500', 24, 4.5, 8.5, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (17, 211, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 231, 11, '40500', 24, 14, 8.5, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (18, 212, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 230, 10, '40500', 24, 7, 8.5, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (19, 213, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 6, 228, 8, '40500', 24, 13, 13, 'V', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (20, 214, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 223, 4, '40500', 24, 3, 7.5, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (21, 215, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 222, 3, '40500', 24, 12, 7.5, 'N', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (22, 216, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 219, 2, '40500', 24, 0, 2, 'E', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (23, 217, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 218, 1, '40500', 24, 4, 2, 'E', 140.875, 16, 8.8046875, 16, 4, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (24, 253, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 257, 26, '40501', 25, 4.5, 11.25, 'V', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (25, 254, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 256, 25, '40501', 25, 18, 11.25, 'V', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (26, 255, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 13, 245, 19, '40501', 25, 12, 12, 'V', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (27, 256, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 261, 29, '40501', 25, 4, 4, 'E', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (28, 257, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 260, 28, '40501', 25, 4, 4, 'E', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (29, 258, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 18, 258, 27, '40501', 25, 8, 8, 'N', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (30, 259, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 251, 24, '40501', 25, 6, 8, 'N', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (31, 260, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 250, 23, '40501', 25, 10, 8, 'N', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (32, 261, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 15, 248, 22, '40501', 25, 10, 10, 'V', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (33, 262, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 14, 246, 21, '40501', 25, 4.25, 4.25, 'E', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (34, 263, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 12, 242, 18, '40501', 25, 14, 14, 'V', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (35, 218, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 20, 226, 5, '40501', 25, 6, 6, 'N', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (36, 219, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 10, 240, 16, '40501', 25, 8, 8, 'N', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (37, 220, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 9, 238, 15, '40501', 25, 14, 14, 'V', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (38, 221, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 8, 236, 14, '40501', 25, 7, 7, 'N', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (39, 222, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 232, 9, '40501', 25, 4.3, 9.1, 'N', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (40, 223, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 231, 11, '40501', 25, 15, 9.1, 'N', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (41, 224, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 230, 10, '40501', 25, 8, 9.1, 'N', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (42, 225, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 6, 228, 8, '40501', 25, 12, 12, 'V', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (43, 226, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 223, 4, '40501', 25, 2, 10.5, 'V', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (44, 227, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 222, 3, '40501', 25, 19, 10.5, 'V', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (45, 228, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 219, 2, '40501', 25, 0, 4.75, 'E', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (46, 229, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 218, 1, '40501', 25, 9.5, 4.75, 'E', 142.85, 16, 8.928125, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (47, 264, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 257, 26, '40502', 26, 6.25, 10.125, 'V', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (48, 265, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 17, 256, 25, '40502', 26, 14, 10.125, 'V', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (49, 266, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 13, 245, 19, '40502', 26, 9, 9, 'N', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (50, 267, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 261, 29, '40502', 26, 2, 2.5, 'E', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (51, 268, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 19, 260, 28, '40502', 26, 3, 2.5, 'E', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (52, 269, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 18, 258, 27, '40502', 26, 7, 7, 'N', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (53, 270, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 251, 24, '40502', 26, 10, 12, 'V', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (54, 271, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 16, 250, 23, '40502', 26, 14, 12, 'V', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (55, 272, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 15, 248, 22, '40502', 26, 10.5, 10.5, 'V', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (56, 273, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 14, 246, 21, '40502', 26, 10, 10, 'V', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (57, 274, 5, 4, 2, 10, 2, 'Evaluation 2', 'eval', NULL, 1, 12, 242, 18, '40502', 26, 15, 15, 'V', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (58, 230, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 20, 226, 5, '40502', 26, 5, 5, 'E', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (59, 231, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 10, 240, 16, '40502', 26, 10, 10, 'V', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (60, 232, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 9, 238, 15, '40502', 26, 13.25, 13.25, 'V', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (61, 233, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 8, 236, 14, '40502', 26, 6, 6, 'N', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (62, 234, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 232, 9, '40502', 26, 2, 8.083333333333334, 'N', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (63, 235, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 231, 11, '40502', 26, 9.75, 8.083333333333334, 'N', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (64, 236, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 7, 230, 10, '40502', 26, 12.5, 8.083333333333334, 'N', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (65, 237, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 6, 228, 8, '40502', 26, 7, 7, 'N', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (66, 238, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 223, 4, '40502', 26, 1, 7.5, 'N', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (67, 239, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 5, 222, 3, '40502', 26, 14, 7.5, 'N', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (68, 240, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 219, 2, '40502', 26, 0, 3.5, 'E', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);
INSERT INTO public.resultats_avant_repechage VALUES (69, 241, 5, 4, 2, 9, 1, 'Evaluation 1', 'eval', NULL, 1, 1, 218, 1, '40502', 26, 7, 3.5, 'E', 136.45833333333334, 16, 8.528645833333334, 16, 7, 12, 3, 'repechage', NULL, NULL);


--
-- Data for Name: resultats_definitifs; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.resultats_definitifs VALUES (139, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 24, '40500', NULL, 'passant', 1, 1, 2, 2, 0, 'E', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Structure et fonction des biomolécules', 'strucuture prof willy', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (140, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 24, '40500', NULL, 'passant', 1, 1, 1, 2, 4, 'E', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Structure et fonction des biomolécules', 'structure prof Raobela', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (141, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 1, 1, 2, 4.75, 0, 'E', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Structure et fonction des biomolécules', 'strucuture prof willy', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (142, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 1, 1, 1, 4.75, 9.5, 'E', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Structure et fonction des biomolécules', 'structure prof Raobela', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (145, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 24, '40500', NULL, 'passant', 5, 1, 4, 7.5, 3, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Biologie cellulaire et tissus', 'Biologie cellulaire prof Y', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (146, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 24, '40500', NULL, 'passant', 5, 1, 3, 7.5, 12, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Biologie cellulaire et tissus', 'Biologie cellulaire prof X', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (147, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 5, 1, 4, 10.5, 2, 'V', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Biologie cellulaire et tissus', 'Biologie cellulaire prof Y', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (148, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 5, 1, 3, 10.5, 19, 'V', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Biologie cellulaire et tissus', 'Biologie cellulaire prof X', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (151, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 24, '40500', NULL, 'passant', 6, 1, 8, 13, 13, 'V', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Physiologie', 'physiologie', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (152, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 6, 1, 8, 12, 12, 'V', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Physiologie', 'physiologie', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (154, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 24, '40500', NULL, 'passant', 7, 1, 9, 8.5, 4.5, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Mathématiques-Biophysique-Statistiques', 'Mathématiques', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (155, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 24, '40500', NULL, 'passant', 7, 1, 11, 8.5, 14, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Mathématiques-Biophysique-Statistiques', 'Statistiques', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (156, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 24, '40500', NULL, 'passant', 7, 1, 10, 8.5, 7, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Mathématiques-Biophysique-Statistiques', 'biophysique', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (157, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 7, 1, 9, 9.1, 4.3, 'N', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Mathématiques-Biophysique-Statistiques', 'Mathématiques', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (158, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 7, 1, 11, 9.1, 15, 'N', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Mathématiques-Biophysique-Statistiques', 'Statistiques', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (159, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 7, 1, 10, 9.1, 8, 'N', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Mathématiques-Biophysique-Statistiques', 'biophysique', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (163, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 24, '40500', NULL, 'passant', 8, 1, 14, 9, 9, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Gestion', 'Gestion', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (164, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 8, 1, 14, 7, 7, 'N', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Gestion', 'Gestion', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (166, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'repe', 24, '40500', NULL, 'passant', 9, 1, 15, 18, 18, 'V', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Anglais', 'Anglais', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (167, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 9, 1, 15, 14, 14, 'V', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Anglais', 'Anglais', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (169, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 24, '40500', NULL, 'passant', 10, 1, 16, 5, 5, 'E', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Français médical', 'Français Médical', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (170, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 10, 1, 16, 8, 8, 'N', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Français médical', 'Français Médical', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (172, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 24, '40500', NULL, 'passant', 20, 1, 5, 7, 7, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Ethique-Deonthologie-Méthodologie de la recherche', 'Ethique-DeonthologieMéthodologie de la recherche', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (173, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 25, '40501', NULL, 'passant', 20, 1, 5, 6, 6, 'N', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Ethique-Deonthologie-Méthodologie de la recherche', 'Ethique-DeonthologieMéthodologie de la recherche', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (175, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 24, '40500', NULL, 'passant', 12, 1, 18, 18, 18, 'V', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Santé publique', 'Santé publique', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (176, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 25, '40501', NULL, 'passant', 12, 1, 18, 14, 14, 'V', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Santé publique', 'Santé publique', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (178, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 24, '40500', NULL, 'passant', 13, 1, 19, 14, 14, 'V', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Thérapeutique chirurgicale', 'Thérapeutique chirurgicale', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (179, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 25, '40501', NULL, 'passant', 13, 1, 19, 12, 12, 'V', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Thérapeutique chirurgicale', 'Thérapeutique chirurgicale', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (181, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 24, '40500', NULL, 'passant', 14, 1, 21, 6.5, 6.5, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Thérapeutique médicale', 'Thérapeutique médicale', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (182, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 25, '40501', NULL, 'passant', 14, 1, 21, 4.25, 4.25, 'E', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Thérapeutique médicale', 'Thérapeutique médicale', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (184, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 24, '40500', NULL, 'passant', 15, 1, 22, 8.5, 8.5, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Médecine opératoire', 'Médecine opératoire', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (185, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 25, '40501', NULL, 'passant', 15, 1, 22, 10, 10, 'V', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Médecine opératoire', 'Médecine opératoire', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (187, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 24, '40500', NULL, 'passant', 16, 1, 24, 4.125, 5, 'E', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Urologie', 'Urologie 2', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (188, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 24, '40500', NULL, 'passant', 16, 1, 23, 4.125, 3.25, 'E', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Urologie', 'Urologie 1', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (189, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 25, '40501', NULL, 'passant', 16, 1, 24, 8, 6, 'N', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Urologie', 'Urologie 2', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (190, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 25, '40501', NULL, 'passant', 16, 1, 23, 8, 10, 'N', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Urologie', 'Urologie 1', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (193, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 24, '40500', NULL, 'passant', 17, 1, 26, 7.25, 7.5, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Réanimation médicale', 'Réanimation Médicale Ec 2', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (194, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 24, '40500', NULL, 'passant', 17, 1, 25, 7.25, 7, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Réanimation médicale', 'Réanimation médicale EC 1', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (195, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 25, '40501', NULL, 'passant', 17, 1, 26, 11.25, 4.5, 'V', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Réanimation médicale', 'Réanimation Médicale Ec 2', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (196, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 25, '40501', NULL, 'passant', 17, 1, 25, 11.25, 18, 'V', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Réanimation médicale', 'Réanimation médicale EC 1', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (199, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 24, '40500', NULL, 'passant', 18, 1, 27, 14, 14, 'V', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Pédiatrie', 'Pédiatire', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (200, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 25, '40501', NULL, 'passant', 18, 1, 27, 8, 8, 'N', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Pédiatrie', 'Pédiatire', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (202, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 24, '40500', NULL, 'passant', 19, 1, 29, 8.5, 7, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Médecine Légale', 'Médecine Légale EC2', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (203, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 24, '40500', NULL, 'passant', 19, 1, 28, 8.5, 10, 'N', 150.875, 16, 9.4296875, 10, 16, 12, 5, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'ANDRIAMANOHISOA', 'Andiva', '2001-03-21', 'Avaradoha', 'Médecine Légale', 'Médecine Légale EC 1', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (204, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 25, '40501', NULL, 'passant', 19, 1, 29, 4, 4, 'E', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Médecine Légale', 'Médecine Légale EC2', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (205, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 25, '40501', NULL, 'passant', 19, 1, 28, 4, 4, 'E', 142.85, 16, 8.928125, 10, 16, 12, 7, 3, 'redoublant', 2, '2027-2028', 'Anesthésie', 'NIVEAU L2', 2, '1', 'JEAN BE', 'Pierre', '2001-08-22', 'Antsiranana', 'Médecine Légale', 'Médecine Légale EC 1', NULL, NULL, NULL, NULL);
INSERT INTO public.resultats_definitifs VALUES (143, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 1, 1, 2, 3.5, 0, 'E', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Structure et fonction des biomolécules', 'strucuture prof willy', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (144, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 1, 1, 1, 3.5, 7, 'E', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Structure et fonction des biomolécules', 'structure prof Raobela', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (149, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 5, 1, 4, 7.5, 1, 'N', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Biologie cellulaire et tissus', 'Biologie cellulaire prof Y', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (150, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 5, 1, 3, 7.5, 14, 'N', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Biologie cellulaire et tissus', 'Biologie cellulaire prof X', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (153, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 6, 1, 8, 7, 7, 'N', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Physiologie', 'physiologie', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (160, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 7, 1, 9, 8.083333333333334, 2, 'N', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Mathématiques-Biophysique-Statistiques', 'Mathématiques', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (161, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 7, 1, 11, 8.083333333333334, 9.75, 'N', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Mathématiques-Biophysique-Statistiques', 'Statistiques', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (162, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 7, 1, 10, 8.083333333333334, 12.5, 'N', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Mathématiques-Biophysique-Statistiques', 'biophysique', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (165, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 8, 1, 14, 6, 6, 'N', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Gestion', 'Gestion', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (168, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 9, 1, 15, 13.25, 13.25, 'V', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Anglais', 'Anglais', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (171, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 10, 1, 16, 10, 10, 'V', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Français médical', 'Français Médical', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (174, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 9, 1, 'Evaluation 1', 'eval', 26, '40502', NULL, 'passant', 20, 1, 5, 5, 5, 'E', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Ethique-Deonthologie-Méthodologie de la recherche', 'Ethique-DeonthologieMéthodologie de la recherche', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (177, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 26, '40502', NULL, 'passant', 12, 1, 18, 15, 15, 'V', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Santé publique', 'Santé publique', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (180, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 26, '40502', NULL, 'passant', 13, 1, 19, 9, 9, 'N', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Thérapeutique chirurgicale', 'Thérapeutique chirurgicale', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (183, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 26, '40502', NULL, 'passant', 14, 1, 21, 10, 10, 'V', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Thérapeutique médicale', 'Thérapeutique médicale', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (186, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 26, '40502', NULL, 'passant', 15, 1, 22, 10.5, 10.5, 'V', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Médecine opératoire', 'Médecine opératoire', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (191, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 26, '40502', NULL, 'passant', 16, 1, 24, 12, 10, 'V', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Urologie', 'Urologie 2', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (192, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 26, '40502', NULL, 'passant', 16, 1, 23, 12, 14, 'V', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Urologie', 'Urologie 1', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (197, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 26, '40502', NULL, 'passant', 17, 1, 26, 10.125, 6.25, 'V', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Réanimation médicale', 'Réanimation Médicale Ec 2', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (198, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 26, '40502', NULL, 'passant', 17, 1, 25, 10.125, 14, 'V', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Réanimation médicale', 'Réanimation médicale EC 1', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (201, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 26, '40502', NULL, 'passant', 18, 1, 27, 7, 7, 'N', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Pédiatrie', 'Pédiatire', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (206, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 26, '40502', NULL, 'passant', 19, 1, 29, 2.5, 2, 'E', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Médecine Légale', 'Médecine Légale EC2', NULL, NULL, NULL, true);
INSERT INTO public.resultats_definitifs VALUES (207, 5, 4, 2, 1, 'NIVEAU L2', 2, NULL, 10, 2, 'Evaluation 2', 'eval', 26, '40502', NULL, 'passant', 19, 1, 28, 2.5, 3, 'E', 136.45833333333334, 16, 8.528645833333334, 10, 16, 12, 7, 3, 'passant', 3, '2027-2028', 'Anesthésie', 'NIVEAU L3', 3, '1', 'RAKOTOBE', 'Paul', '2004-09-19', 'Amboasary', 'Médecine Légale', 'Médecine Légale EC 1', NULL, NULL, NULL, true);


--
-- Data for Name: role; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.role VALUES (1, 'admin', NULL, NULL, 70);
INSERT INTO public.role VALUES (2, 'doyen', NULL, NULL, 60);
INSERT INTO public.role VALUES (3, 'vice-doyen', NULL, NULL, 50);
INSERT INTO public.role VALUES (4, 'secrétaire principal', NULL, NULL, 40);
INSERT INTO public.role VALUES (5, 'chef de service', NULL, NULL, 30);
INSERT INTO public.role VALUES (6, 'adjoint', NULL, NULL, 20);
INSERT INTO public.role VALUES (7, 'chef de division scolarite', NULL, NULL, 10);
INSERT INTO public.role VALUES (8, 'chef de division', NULL, NULL, 0);


--
-- Data for Name: selectionnes; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.selectionnes VALUES (1, 'ADRIANE NOMENJANAHARY', 'MIRANA MARTINAH', '4030419', 1, 3, true, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (17, 'ANDRIAMAHEFATINA', 'DANIELA', '4000532', 1, 3, true, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (3, 'AICHA FANILOSOA', 'MENDRIKA FIANDRIANANA', '4155036', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (4, 'AINAHERILANTO', 'FAMENO', '4180061', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (5, 'toto', '', '4563', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (6, 'titi', 'HERY FINARITRA', '3368072', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (7, 'AMBOARA', 'NOMENAIVO ZO HARIVONY DIAMONDRA IRINA JENNYVA', '3440002', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (8, 'AMBOARANTSOA', 'HOSANA RODRIGO', '7896', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (9, 'ANDIAMANANARIVO', 'MIHAJA NANTENAINA', '4065050', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (10, 'ANDONIAINA', 'LALAINARIVONY MAMITIANA', '4030384', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (11, 'tutu', 'TSAROANA OMEGA', '4566', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (12, 'ANDRIAMAHAIAVISOA', 'REBECC LARISSA', '4020056', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (13, 'yuyu', 'ONJANIAINA RINAH VALERIEN', '7412', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (14, 'ANDRIAMAHANDRY', 'HENINTSOA STEPHAN', '4180440', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (15, 'ANDRIAMAHARY', 'NARINDRASOA OLIVIA', '3700007', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (18, 'ANDRIAMAHOLY', 'ANDRINIAINA ELIA', '3496019', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (19, 'ANDRIAMALALA', 'HERINIAINA GERVAIS LUCIEN', '3820067', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (20, 'ANDRIAMALALA', 'STEVA NANTENAINA', '4095224', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (21, 'ANDRIAMAMPIADANA', 'TAMBY TSILAVINA', '4020640', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (22, 'ANDRIAMAMPIONONA', 'FARATIANA', '3380189', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (23, 'ANDRIAMANALINA', 'ROJO SEHENO', '4085122', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (24, 'ANDRIAMANAMAHEFA', 'ROVANIAINA FIONONANA JESSICA', '4255087', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (25, 'ANDRIAMANAMIHANTA', 'RARAMPY TAHIRINIAINA', '4075260', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (26, 'ANDRIAMANAMPY', 'FITAHIANA ELISA', '4000179', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (27, 'ANDRIAMANANJARA', 'PARSON', '4075090', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (28, 'ANDRIAMANANTENA', 'HUBERT VAGNEDO', '4000472', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (2, 'AHMAD', 'ARWAT', '4020205', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);
INSERT INTO public.selectionnes VALUES (16, 'ANDRIAMAHEFA', 'EDMOND', '74123', 1, 3, NULL, '2024-10-28 19:57:51', '2024-10-28 19:57:51', 4);


--
-- Data for Name: serie; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.serie VALUES (1, 'C', NULL, NULL);
INSERT INTO public.serie VALUES (2, 'D', NULL, NULL);
INSERT INTO public.serie VALUES (3, 'S', NULL, NULL);
INSERT INTO public.serie VALUES (4, 'Techniques Agricoles', NULL, NULL);
INSERT INTO public.serie VALUES (5, 'Techniques d''Elevage', NULL, NULL);


--
-- Data for Name: session_examen; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.session_examen VALUES (1, 'Evaluation 1', NULL, NULL, 'eval');
INSERT INTO public.session_examen VALUES (2, 'Evaluation 2', NULL, NULL, 'eval');
INSERT INTO public.session_examen VALUES (3, 'Repechage', NULL, NULL, 'repe');
INSERT INTO public.session_examen VALUES (10, 'Concours PACES', NULL, NULL, 'conc');


--
-- Data for Name: transferts_autorises; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.transferts_autorises VALUES (1, 4);
INSERT INTO public.transferts_autorises VALUES (1, 5);
INSERT INTO public.transferts_autorises VALUES (1, 6);
INSERT INTO public.transferts_autorises VALUES (2, 4);
INSERT INTO public.transferts_autorises VALUES (2, 5);
INSERT INTO public.transferts_autorises VALUES (2, 6);
INSERT INTO public.transferts_autorises VALUES (3, 4);
INSERT INTO public.transferts_autorises VALUES (3, 5);
INSERT INTO public.transferts_autorises VALUES (3, 6);
INSERT INTO public.transferts_autorises VALUES (3, 4);
INSERT INTO public.transferts_autorises VALUES (3, 5);
INSERT INTO public.transferts_autorises VALUES (3, 6);
INSERT INTO public.transferts_autorises VALUES (4, 2);
INSERT INTO public.transferts_autorises VALUES (4, 3);
INSERT INTO public.transferts_autorises VALUES (5, 2);
INSERT INTO public.transferts_autorises VALUES (5, 3);
INSERT INTO public.transferts_autorises VALUES (6, 2);
INSERT INTO public.transferts_autorises VALUES (6, 3);
INSERT INTO public.transferts_autorises VALUES (7, 2);
INSERT INTO public.transferts_autorises VALUES (7, 3);
INSERT INTO public.transferts_autorises VALUES (8, 2);
INSERT INTO public.transferts_autorises VALUES (8, 3);
INSERT INTO public.transferts_autorises VALUES (9, 2);
INSERT INTO public.transferts_autorises VALUES (9, 3);
INSERT INTO public.transferts_autorises VALUES (10, 2);
INSERT INTO public.transferts_autorises VALUES (10, 3);
INSERT INTO public.transferts_autorises VALUES (11, 2);
INSERT INTO public.transferts_autorises VALUES (11, 3);


--
-- Data for Name: ue_ec_parcours_niveau_au; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.ue_ec_parcours_niveau_au VALUES (222, 1, 9, 4, 2, 5, 3, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (223, 1, 9, 4, 2, 5, 4, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (226, 1, 9, 4, 2, 20, 5, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (228, 1, 9, 4, 2, 6, 8, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (230, 1, 9, 4, 2, 7, 10, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (231, 1, 9, 4, 2, 7, 11, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (232, 1, 9, 4, 2, 7, 9, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (236, 1, 9, 4, 2, 8, 14, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (238, 1, 9, 4, 2, 9, 15, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (240, 1, 9, 4, 2, 10, 16, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (242, 1, 10, 4, 2, 12, 18, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (244, 1, 11, 4, 2, 13, 19, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (246, 1, 10, 4, 2, 14, 21, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (248, 1, 10, 4, 2, 15, 22, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (250, 1, 10, 4, 2, 16, 23, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (251, 1, 10, 4, 2, 16, 24, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (254, 1, 11, 4, 2, 17, 25, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (255, 1, 11, 4, 2, 17, 26, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (258, 1, 10, 4, 2, 18, 27, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (260, 1, 10, 4, 2, 19, 28, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (261, 1, 10, 4, 2, 19, 29, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (264, 1, 16, 1, 1, 4, 6, 6, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (266, 1, 16, 1, 1, 5, 12, 6, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (224, 1, 11, 4, 2, 5, 3, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (225, 1, 11, 4, 2, 5, 4, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (227, 1, 11, 4, 2, 20, 5, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (229, 1, 11, 4, 2, 6, 8, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (233, 1, 11, 4, 2, 7, 9, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (234, 1, 11, 4, 2, 7, 10, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (235, 1, 11, 4, 2, 7, 11, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (237, 1, 11, 4, 2, 8, 14, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (239, 1, 11, 4, 2, 9, 15, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (241, 1, 11, 4, 2, 10, 16, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (243, 1, 11, 4, 2, 12, 18, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (245, 1, 10, 4, 2, 13, 19, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (247, 1, 11, 4, 2, 14, 21, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (249, 1, 11, 4, 2, 15, 22, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (252, 1, 11, 4, 2, 16, 23, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (253, 1, 11, 4, 2, 16, 24, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (256, 1, 10, 4, 2, 17, 25, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (257, 1, 10, 4, 2, 17, 26, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (259, 1, 11, 4, 2, 18, 27, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (262, 1, 11, 4, 2, 19, 28, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (263, 1, 11, 4, 2, 19, 29, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (265, 1, 16, 1, 1, 9, 15, 6, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (267, 1, 16, 1, 1, 21, 30, 6, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (218, 1, 9, 4, 2, 1, 1, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (219, 1, 9, 4, 2, 1, 2, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (220, 1, 11, 4, 2, 1, 1, 5, NULL, NULL);
INSERT INTO public.ue_ec_parcours_niveau_au VALUES (221, 1, 11, 4, 2, 1, 2, 5, NULL, NULL);


--
-- Data for Name: unite_enseignement; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.unite_enseignement VALUES (1, 'Structure et fonction des biomolécules', '2024-10-11 15:05:20', '2024-10-11 15:05:20');
INSERT INTO public.unite_enseignement VALUES (4, 'Anatomie viscérale', '2024-10-14 12:01:54', '2024-10-14 12:01:54');
INSERT INTO public.unite_enseignement VALUES (5, 'Biologie cellulaire et tissus', '2024-10-14 12:40:56', '2024-10-14 12:40:56');
INSERT INTO public.unite_enseignement VALUES (6, 'Physiologie', '2024-10-14 12:42:03', '2024-10-14 12:42:03');
INSERT INTO public.unite_enseignement VALUES (7, 'Mathématiques-Biophysique-Statistiques', '2024-10-14 12:44:27', '2024-10-14 12:44:27');
INSERT INTO public.unite_enseignement VALUES (8, 'Gestion', '2024-11-09 08:16:18', '2024-11-09 08:16:18');
INSERT INTO public.unite_enseignement VALUES (9, 'Anglais', '2024-11-09 08:16:50', '2024-11-09 08:16:50');
INSERT INTO public.unite_enseignement VALUES (10, 'Français médical', '2024-11-09 08:17:27', '2024-11-09 08:17:27');
INSERT INTO public.unite_enseignement VALUES (11, 'Pharmacologie', '2024-11-14 10:51:38', '2024-11-14 10:51:38');
INSERT INTO public.unite_enseignement VALUES (12, 'Santé publique', '2024-11-14 11:07:07', '2024-11-14 11:07:07');
INSERT INTO public.unite_enseignement VALUES (13, 'Thérapeutique chirurgicale', '2024-11-14 11:08:49', '2024-11-14 11:08:49');
INSERT INTO public.unite_enseignement VALUES (14, 'Thérapeutique médicale', '2024-11-14 11:10:12', '2024-11-14 11:10:12');
INSERT INTO public.unite_enseignement VALUES (15, 'Médecine opératoire', '2024-11-14 11:10:51', '2024-11-14 11:10:51');
INSERT INTO public.unite_enseignement VALUES (16, 'Urologie', '2024-11-14 11:12:12', '2024-11-14 11:12:12');
INSERT INTO public.unite_enseignement VALUES (17, 'Réanimation médicale', '2024-11-14 11:13:16', '2024-11-14 11:13:16');
INSERT INTO public.unite_enseignement VALUES (18, 'Pédiatrie', '2024-11-14 11:13:58', '2024-11-14 11:13:58');
INSERT INTO public.unite_enseignement VALUES (19, 'Médecine Légale', '2024-11-14 11:15:12', '2024-11-14 11:15:12');
INSERT INTO public.unite_enseignement VALUES (20, 'Ethique-Deonthologie-Méthodologie de la recherche', '2024-12-03 08:20:21', '2024-12-03 08:20:21');
INSERT INTO public.unite_enseignement VALUES (21, 'UE non spécifique', '2025-01-08 15:26:07', '2025-01-08 15:26:07');


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.users VALUES (2, 'admin', 'servicescol213@gmail.com', NULL, '$2y$12$QFTtFQNEtlae5gzpQn61MuVChNdOEyG1/EVVwfPOCHJyAHcDoopoa', NULL, NULL, NULL, 1, NULL);
INSERT INTO public.users VALUES (3, 'Renato Michel', 'renatorakoto27@gmail.com', NULL, '$2y$12$OvH1QdAR56qx.Yvq/DRIRe5QQicgrFmGtljdpiKvAhb36b1VnDJAu', NULL, '2024-10-11 08:53:45', '2024-10-11 08:53:45', 8, NULL);
INSERT INTO public.users VALUES (4, 'Renato Michel', 'adjoint@mail.com', NULL, '$2y$12$EmuffRfiXSn/HshA6fmcxunm97f2dThIdqA2c6R0u7eHUYljL0ucK', NULL, '2024-10-15 08:48:23', '2024-10-15 08:48:23', 6, NULL);
INSERT INTO public.users VALUES (5, 'Emmanuel Guy', 'emmanuel.guy@facmed', NULL, '$2y$12$by2U4xJKustfHtaQ3g4BU.lZywq2zVOEI0353.evbvSxmXYQjGrTu', NULL, '2024-10-16 07:41:08', '2024-10-16 07:41:08', 3, NULL);
INSERT INTO public.users VALUES (6, 'Michele Ruana', 'michleruana@yahoo.fr', NULL, '$2y$12$EAvDE7FbaIRBzJUhFsihMOHDmBzo/bFC5PkwN6etUtMe9gOtLUolm', NULL, '2024-10-24 06:46:22', '2024-10-24 07:29:45', 4, NULL);


--
-- Name: au_id_au_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.au_id_au_seq', 6, true);


--
-- Name: autres_etablissements_id_autre_etablissement_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.autres_etablissements_id_autre_etablissement_seq', 5, true);


--
-- Name: correspondance_mention_ent_id_correspondance_mention_ent_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.correspondance_mention_ent_id_correspondance_mention_ent_seq', 4, true);


--
-- Name: correspondance_parcours_ent_id_correspondance_parcours_ent_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.correspondance_parcours_ent_id_correspondance_parcours_ent_seq', 8, true);


--
-- Name: cte_codes_barres_en_plus_id_cte_codes_barres_en_plus_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.cte_codes_barres_en_plus_id_cte_codes_barres_en_plus_seq', 3, true);


--
-- Name: element_constitutif_id_element_constitutif_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.element_constitutif_id_element_constitutif_seq', 30, true);


--
-- Name: etudiants_id_etudiants_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.etudiants_id_etudiants_seq', 26, true);


--
-- Name: examen_par_au_id_examen_par_au_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.examen_par_au_id_examen_par_au_seq', 16, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: inscription_id_inscription_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.inscription_id_inscription_seq', 29, true);


--
-- Name: mention_ent_id_mention_ent_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.mention_ent_id_mention_ent_seq', 8, true);


--
-- Name: mention_id_mention_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.mention_id_mention_seq', 4, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 544, true);


--
-- Name: moyenne_admission_id_moyenne_admission_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.moyenne_admission_id_moyenne_admission_seq', 1, true);


--
-- Name: nationalites_id_nationalites_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.nationalites_id_nationalites_seq', 6, true);


--
-- Name: niveau_id_niveau_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.niveau_id_niveau_seq', 6, true);


--
-- Name: note_eliminatoire_id_note_eliminatoire_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.note_eliminatoire_id_note_eliminatoire_seq', 2, true);


--
-- Name: note_eval_id_note_eval_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.note_eval_id_note_eval_seq', 274, true);


--
-- Name: note_max_id_note_max_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.note_max_id_note_max_seq', 4, true);


--
-- Name: note_validation_ue_id_note_validation_ue_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.note_validation_ue_id_note_validation_ue_seq', 2, true);


--
-- Name: operation_par_au_id_operation_par_au_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.operation_par_au_id_operation_par_au_seq', 1, true);


--
-- Name: operation_par_deliberation_id_operation_sur_deliberation_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.operation_par_deliberation_id_operation_sur_deliberation_seq', 7, true);


--
-- Name: operation_par_examen_id_operation_par_examen_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.operation_par_examen_id_operation_par_examen_seq', 13, true);


--
-- Name: operation_par_import_resultat_id_operation_par_import_resul_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.operation_par_import_resultat_id_operation_par_import_resul_seq', 1, false);


--
-- Name: operation_sur_examen_par_au_id_operation_sur_examen_par_au_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.operation_sur_examen_par_au_id_operation_sur_examen_par_au_seq', 1, false);


--
-- Name: parcours_ent_id_parcours_ent_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.parcours_ent_id_parcours_ent_seq', 8, true);


--
-- Name: parcours_id_parcours_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.parcours_id_parcours_seq', 11, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: pourcentage_admission_id_pourcentage_admission_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.pourcentage_admission_id_pourcentage_admission_seq', 1, true);


--
-- Name: province_id_province_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.province_id_province_seq', 7, true);


--
-- Name: resultats_avant_deliberation_id_resultat_avant_deliberation_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.resultats_avant_deliberation_id_resultat_avant_deliberation_seq', 207, true);


--
-- Name: resultats_avant_repechage_id_resultats_avant_repechage_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.resultats_avant_repechage_id_resultats_avant_repechage_seq', 69, true);


--
-- Name: resultats_definitifs_id_resultat_definitif_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.resultats_definitifs_id_resultat_definitif_seq', 207, true);


--
-- Name: role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.role_id_seq', 8, true);


--
-- Name: selectionnes_id_selectionnes_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.selectionnes_id_selectionnes_seq', 28, true);


--
-- Name: serie_id_serie_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.serie_id_serie_seq', 5, true);


--
-- Name: session_examen_id_session_examen_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.session_examen_id_session_examen_seq', 12, true);


--
-- Name: ue_ec_parcours_niveau_au_id_ue_ec_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ue_ec_parcours_niveau_au_id_ue_ec_seq', 267, true);


--
-- Name: unite_enseignement_id_unite_enseignement_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.unite_enseignement_id_unite_enseignement_seq', 21, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 6, true);


--
-- Name: au au_intitule_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.au
    ADD CONSTRAINT au_intitule_unique UNIQUE (intitule);


--
-- Name: au au_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.au
    ADD CONSTRAINT au_pkey PRIMARY KEY (id_au);


--
-- Name: autres_etablissements autres_etablissements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.autres_etablissements
    ADD CONSTRAINT autres_etablissements_pkey PRIMARY KEY (id_autre_etablissement);


--
-- Name: barcode_matricule barcode_matricule_id_ue_ec_matricule_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.barcode_matricule
    ADD CONSTRAINT barcode_matricule_id_ue_ec_matricule_unique UNIQUE (id_ue_ec, matricule);


--
-- Name: barcode_matricule barcode_matricule_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.barcode_matricule
    ADD CONSTRAINT barcode_matricule_pkey PRIMARY KEY (id_ue_ec, numero);


--
-- Name: barcode_note barcode_note_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.barcode_note
    ADD CONSTRAINT barcode_note_pkey PRIMARY KEY (id_ue_ec, numero);


--
-- Name: correspondance_mention_ent correspondance_mention_ent_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.correspondance_mention_ent
    ADD CONSTRAINT correspondance_mention_ent_pkey PRIMARY KEY (id_correspondance_mention_ent);


--
-- Name: correspondance_parcours_ent correspondance_parcours_ent_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.correspondance_parcours_ent
    ADD CONSTRAINT correspondance_parcours_ent_pkey PRIMARY KEY (id_correspondance_parcours_ent);


--
-- Name: cte_codes_barres_en_plus cte_codes_barres_en_plus_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cte_codes_barres_en_plus
    ADD CONSTRAINT cte_codes_barres_en_plus_pkey PRIMARY KEY (id_cte_codes_barres_en_plus);


--
-- Name: element_constitutif element_constitutif_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.element_constitutif
    ADD CONSTRAINT element_constitutif_pkey PRIMARY KEY (id_element_constitutif);


--
-- Name: etudiants etudiants_im_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.etudiants
    ADD CONSTRAINT etudiants_im_unique UNIQUE (im);


--
-- Name: etudiants etudiants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.etudiants
    ADD CONSTRAINT etudiants_pkey PRIMARY KEY (id_etudiants);


--
-- Name: examen_par_au examen_par_au_id_au_id_session_examen_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.examen_par_au
    ADD CONSTRAINT examen_par_au_id_au_id_session_examen_unique UNIQUE (id_au, id_session_examen);


--
-- Name: examen_par_au examen_par_au_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.examen_par_au
    ADD CONSTRAINT examen_par_au_pkey PRIMARY KEY (id_examen_par_au);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: inscription inscription_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inscription
    ADD CONSTRAINT inscription_pkey PRIMARY KEY (id_inscription);


--
-- Name: mention_ent mention_ent_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mention_ent
    ADD CONSTRAINT mention_ent_pkey PRIMARY KEY (id_mention_ent);


--
-- Name: mention mention_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.mention
    ADD CONSTRAINT mention_pkey PRIMARY KEY (id_mention);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: moyenne_admission moyenne_admission_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.moyenne_admission
    ADD CONSTRAINT moyenne_admission_pkey PRIMARY KEY (id_moyenne_admission);


--
-- Name: nationalites nationalites_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.nationalites
    ADD CONSTRAINT nationalites_pkey PRIMARY KEY (id_nationalites);


--
-- Name: niveau niveau_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.niveau
    ADD CONSTRAINT niveau_pkey PRIMARY KEY (id_niveau);


--
-- Name: note_eliminatoire note_eliminatoire_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eliminatoire
    ADD CONSTRAINT note_eliminatoire_pkey PRIMARY KEY (id_note_eliminatoire);


--
-- Name: note_eval note_eval_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eval
    ADD CONSTRAINT note_eval_pkey PRIMARY KEY (id_note_eval);


--
-- Name: note_max note_max_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_max
    ADD CONSTRAINT note_max_pkey PRIMARY KEY (id_note_max);


--
-- Name: note_validation_ue note_validation_ue_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_validation_ue
    ADD CONSTRAINT note_validation_ue_pkey PRIMARY KEY (id_note_validation_ue);


--
-- Name: operation_par_au operation_par_au_id_au_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_au
    ADD CONSTRAINT operation_par_au_id_au_unique UNIQUE (id_au);


--
-- Name: operation_par_au operation_par_au_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_au
    ADD CONSTRAINT operation_par_au_pkey PRIMARY KEY (id_operation_par_au);


--
-- Name: operation_par_deliberation operation_par_deliberation_id_au_id_parcours_id_niveau_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_deliberation
    ADD CONSTRAINT operation_par_deliberation_id_au_id_parcours_id_niveau_unique UNIQUE (id_au, id_parcours, id_niveau);


--
-- Name: operation_par_deliberation operation_par_deliberation_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_deliberation
    ADD CONSTRAINT operation_par_deliberation_pkey PRIMARY KEY (id_operation_sur_deliberation);


--
-- Name: operation_par_examen operation_par_examen_id_examen_par_au_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_id_examen_par_au_unique UNIQUE (id_examen_par_au);


--
-- Name: operation_par_examen operation_par_examen_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_pkey PRIMARY KEY (id_operation_par_examen);


--
-- Name: operation_par_import_resultat_paces operation_par_import_resultat_paces_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_import_resultat_paces
    ADD CONSTRAINT operation_par_import_resultat_paces_pkey PRIMARY KEY (id_operation_par_import_resultat_paces);


--
-- Name: operation_sur_examen_par_au operation_sur_examen_par_au_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_sur_examen_par_au
    ADD CONSTRAINT operation_sur_examen_par_au_pkey PRIMARY KEY (id_operation_sur_examen_par_au);


--
-- Name: parcours_ent parcours_ent_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parcours_ent
    ADD CONSTRAINT parcours_ent_pkey PRIMARY KEY (id_parcours_ent);


--
-- Name: parcours parcours_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parcours
    ADD CONSTRAINT parcours_pkey PRIMARY KEY (id_parcours);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: pourcentage_admission pourcentage_admission_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pourcentage_admission
    ADD CONSTRAINT pourcentage_admission_pkey PRIMARY KEY (id_pourcentage_admission);


--
-- Name: province province_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.province
    ADD CONSTRAINT province_pkey PRIMARY KEY (id_province);


--
-- Name: resultats_avant_deliberation resultats_avant_deliberation_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_deliberation
    ADD CONSTRAINT resultats_avant_deliberation_pkey PRIMARY KEY (id_resultat_avant_deliberation);


--
-- Name: resultats_avant_repechage resultats_avant_repechage_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage
    ADD CONSTRAINT resultats_avant_repechage_pkey PRIMARY KEY (id_resultats_avant_repechage);


--
-- Name: resultats_definitifs resultats_definitifs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_definitifs
    ADD CONSTRAINT resultats_definitifs_pkey PRIMARY KEY (id_resultat_definitif);


--
-- Name: role role_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT role_pkey PRIMARY KEY (id);


--
-- Name: selectionnes selectionnes_num_bacc_id_parcours_id_au_nom_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.selectionnes
    ADD CONSTRAINT selectionnes_num_bacc_id_parcours_id_au_nom_unique UNIQUE (num_bacc, id_parcours, id_au, nom);


--
-- Name: selectionnes selectionnes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.selectionnes
    ADD CONSTRAINT selectionnes_pkey PRIMARY KEY (id_selectionnes);


--
-- Name: serie serie_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.serie
    ADD CONSTRAINT serie_pkey PRIMARY KEY (id_serie);


--
-- Name: session_examen session_examen_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.session_examen
    ADD CONSTRAINT session_examen_pkey PRIMARY KEY (id_session_examen);


--
-- Name: ue_ec_parcours_niveau_au ue_ec_parcours_niveau_au_id_examen_par_au_id_parcours_id_niveau; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ue_ec_parcours_niveau_au
    ADD CONSTRAINT ue_ec_parcours_niveau_au_id_examen_par_au_id_parcours_id_niveau UNIQUE (id_examen_par_au, id_parcours, id_niveau, id_unite_enseignement, id_element_constitutif, id_au);


--
-- Name: ue_ec_parcours_niveau_au ue_ec_parcours_niveau_au_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ue_ec_parcours_niveau_au
    ADD CONSTRAINT ue_ec_parcours_niveau_au_pkey PRIMARY KEY (id_ue_ec);


--
-- Name: unite_enseignement unite_enseignement_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.unite_enseignement
    ADD CONSTRAINT unite_enseignement_pkey PRIMARY KEY (id_unite_enseignement);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: barcode_matricule_id_ue_ec_verifie_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX barcode_matricule_id_ue_ec_verifie_index ON public.barcode_matricule USING btree (id_ue_ec, verifie);


--
-- Name: barcode_note_id_ue_ec_verifie_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX barcode_note_id_ue_ec_verifie_index ON public.barcode_note USING btree (id_ue_ec, verifie);


--
-- Name: etudiants_id_parcours_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX etudiants_id_parcours_index ON public.etudiants USING btree (id_parcours);


--
-- Name: etudiants_im_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX etudiants_im_index ON public.etudiants USING btree (im);


--
-- Name: inscription_id_au_id_niveau_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX inscription_id_au_id_niveau_index ON public.inscription USING btree (id_au, id_niveau);


--
-- Name: inscription_id_etudiant_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX inscription_id_etudiant_index ON public.inscription USING btree (id_etudiant);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: ue_ec_parcours_niveau_au_id_parcours_id_niveau_id_au_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ue_ec_parcours_niveau_au_id_parcours_id_niveau_id_au_index ON public.ue_ec_parcours_niveau_au USING btree (id_parcours, id_niveau, id_au);


--
-- Name: autres_inscriptions autres_inscriptions_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.autres_inscriptions
    ADD CONSTRAINT autres_inscriptions_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: autres_inscriptions autres_inscriptions_id_etudiants_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.autres_inscriptions
    ADD CONSTRAINT autres_inscriptions_id_etudiants_foreign FOREIGN KEY (id_etudiants) REFERENCES public.etudiants(id_etudiants);


--
-- Name: barcode_matricule barcode_matricule_id_ue_ec_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.barcode_matricule
    ADD CONSTRAINT barcode_matricule_id_ue_ec_foreign FOREIGN KEY (id_ue_ec) REFERENCES public.ue_ec_parcours_niveau_au(id_ue_ec);


--
-- Name: barcode_matricule barcode_matricule_matricule_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.barcode_matricule
    ADD CONSTRAINT barcode_matricule_matricule_foreign FOREIGN KEY (matricule) REFERENCES public.etudiants(im);


--
-- Name: barcode_note barcode_note_id_ue_ec_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.barcode_note
    ADD CONSTRAINT barcode_note_id_ue_ec_foreign FOREIGN KEY (id_ue_ec) REFERENCES public.ue_ec_parcours_niveau_au(id_ue_ec);


--
-- Name: etudiants etudiants_id_agent_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.etudiants
    ADD CONSTRAINT etudiants_id_agent_foreign FOREIGN KEY (id_agent) REFERENCES public.users(id);


--
-- Name: etudiants etudiants_id_au_transfert_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.etudiants
    ADD CONSTRAINT etudiants_id_au_transfert_foreign FOREIGN KEY (id_au_transfert) REFERENCES public.au(id_au);


--
-- Name: etudiants etudiants_id_etablissement_transfert_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.etudiants
    ADD CONSTRAINT etudiants_id_etablissement_transfert_foreign FOREIGN KEY (id_etablissement_transfert) REFERENCES public.autres_etablissements(id_autre_etablissement);


--
-- Name: etudiants etudiants_id_nationalite_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.etudiants
    ADD CONSTRAINT etudiants_id_nationalite_foreign FOREIGN KEY (id_nationalite) REFERENCES public.nationalites(id_nationalites);


--
-- Name: etudiants etudiants_id_niveau_transfert_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.etudiants
    ADD CONSTRAINT etudiants_id_niveau_transfert_foreign FOREIGN KEY (id_niveau_transfert) REFERENCES public.niveau(id_niveau);


--
-- Name: etudiants etudiants_id_parcours_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.etudiants
    ADD CONSTRAINT etudiants_id_parcours_foreign FOREIGN KEY (id_parcours) REFERENCES public.parcours(id_parcours);


--
-- Name: etudiants etudiants_id_province_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.etudiants
    ADD CONSTRAINT etudiants_id_province_foreign FOREIGN KEY (id_province) REFERENCES public.province(id_province);


--
-- Name: etudiants etudiants_id_serie_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.etudiants
    ADD CONSTRAINT etudiants_id_serie_foreign FOREIGN KEY (id_serie) REFERENCES public.serie(id_serie);


--
-- Name: examen_par_au examen_par_au_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.examen_par_au
    ADD CONSTRAINT examen_par_au_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: examen_par_au examen_par_au_id_session_examen_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.examen_par_au
    ADD CONSTRAINT examen_par_au_id_session_examen_foreign FOREIGN KEY (id_session_examen) REFERENCES public.session_examen(id_session_examen);


--
-- Name: inscription inscription_id_agent_annulation_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inscription
    ADD CONSTRAINT inscription_id_agent_annulation_foreign FOREIGN KEY (id_agent_annulation) REFERENCES public.users(id);


--
-- Name: inscription inscription_id_agent_inscription_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inscription
    ADD CONSTRAINT inscription_id_agent_inscription_foreign FOREIGN KEY (id_agent_inscription) REFERENCES public.users(id);


--
-- Name: inscription inscription_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inscription
    ADD CONSTRAINT inscription_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: inscription inscription_id_etudiant_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inscription
    ADD CONSTRAINT inscription_id_etudiant_foreign FOREIGN KEY (id_etudiant) REFERENCES public.etudiants(id_etudiants);


--
-- Name: inscription inscription_id_niveau_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.inscription
    ADD CONSTRAINT inscription_id_niveau_foreign FOREIGN KEY (id_niveau) REFERENCES public.niveau(id_niveau);


--
-- Name: note_eval note_eval_id_element_constitutif_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eval
    ADD CONSTRAINT note_eval_id_element_constitutif_foreign FOREIGN KEY (id_element_constitutif) REFERENCES public.element_constitutif(id_element_constitutif);


--
-- Name: note_eval note_eval_id_etudiants_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eval
    ADD CONSTRAINT note_eval_id_etudiants_foreign FOREIGN KEY (id_etudiants) REFERENCES public.etudiants(id_etudiants);


--
-- Name: note_eval note_eval_id_examen_par_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eval
    ADD CONSTRAINT note_eval_id_examen_par_au_foreign FOREIGN KEY (id_examen_par_au) REFERENCES public.examen_par_au(id_examen_par_au);


--
-- Name: note_eval note_eval_id_niveau_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eval
    ADD CONSTRAINT note_eval_id_niveau_foreign FOREIGN KEY (id_niveau) REFERENCES public.niveau(id_niveau);


--
-- Name: note_eval note_eval_id_parcours_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eval
    ADD CONSTRAINT note_eval_id_parcours_foreign FOREIGN KEY (id_parcours) REFERENCES public.parcours(id_parcours);


--
-- Name: note_eval note_eval_id_session_examen_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eval
    ADD CONSTRAINT note_eval_id_session_examen_foreign FOREIGN KEY (id_session_examen) REFERENCES public.session_examen(id_session_examen);


--
-- Name: note_eval note_eval_id_ue_ec_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eval
    ADD CONSTRAINT note_eval_id_ue_ec_foreign FOREIGN KEY (id_ue_ec) REFERENCES public.ue_ec_parcours_niveau_au(id_ue_ec);


--
-- Name: note_eval note_eval_id_unite_enseignement_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.note_eval
    ADD CONSTRAINT note_eval_id_unite_enseignement_foreign FOREIGN KEY (id_unite_enseignement) REFERENCES public.unite_enseignement(id_unite_enseignement);


--
-- Name: operation_par_au operation_par_au_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_au
    ADD CONSTRAINT operation_par_au_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: operation_par_au operation_par_au_id_user_date_liste_repechage_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_au
    ADD CONSTRAINT operation_par_au_id_user_date_liste_repechage_foreign FOREIGN KEY (id_user_date_liste_repechage) REFERENCES public.users(id);


--
-- Name: operation_par_au operation_par_au_id_user_date_resultats_avant_deliberation_fore; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_au
    ADD CONSTRAINT operation_par_au_id_user_date_resultats_avant_deliberation_fore FOREIGN KEY (id_user_date_resultats_avant_deliberation) REFERENCES public.users(id);


--
-- Name: operation_par_au operation_par_au_id_user_date_resultats_avant_repechage_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_au
    ADD CONSTRAINT operation_par_au_id_user_date_resultats_avant_repechage_foreign FOREIGN KEY (id_user_date_resultats_avant_repechage) REFERENCES public.users(id);


--
-- Name: operation_par_au operation_par_au_id_user_date_resultats_definitifs_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_au
    ADD CONSTRAINT operation_par_au_id_user_date_resultats_definitifs_foreign FOREIGN KEY (id_user_date_resultats_definitifs) REFERENCES public.users(id);


--
-- Name: operation_par_deliberation operation_par_deliberation_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_deliberation
    ADD CONSTRAINT operation_par_deliberation_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: operation_par_deliberation operation_par_deliberation_id_niveau_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_deliberation
    ADD CONSTRAINT operation_par_deliberation_id_niveau_foreign FOREIGN KEY (id_niveau) REFERENCES public.niveau(id_niveau);


--
-- Name: operation_par_deliberation operation_par_deliberation_id_parcours_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_deliberation
    ADD CONSTRAINT operation_par_deliberation_id_parcours_foreign FOREIGN KEY (id_parcours) REFERENCES public.parcours(id_parcours);


--
-- Name: operation_par_deliberation operation_par_deliberation_id_user_date_cloture_deliberation_fo; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_deliberation
    ADD CONSTRAINT operation_par_deliberation_id_user_date_cloture_deliberation_fo FOREIGN KEY (id_user_date_cloture_deliberation) REFERENCES public.users(id);


--
-- Name: operation_par_deliberation operation_par_deliberation_id_user_date_ouverture_deliberation_; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_deliberation
    ADD CONSTRAINT operation_par_deliberation_id_user_date_ouverture_deliberation_ FOREIGN KEY (id_user_date_ouverture_deliberation) REFERENCES public.users(id);


--
-- Name: operation_par_examen operation_par_examen_id_examen_par_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_id_examen_par_au_foreign FOREIGN KEY (id_examen_par_au) REFERENCES public.examen_par_au(id_examen_par_au);


--
-- Name: operation_par_examen operation_par_examen_id_user_date_cloture_saisie_en_tete_foreig; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_id_user_date_cloture_saisie_en_tete_foreig FOREIGN KEY (id_user_date_cloture_saisie_en_tete) REFERENCES public.users(id);


--
-- Name: operation_par_examen operation_par_examen_id_user_date_cloture_saisie_note_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_id_user_date_cloture_saisie_note_foreign FOREIGN KEY (id_user_date_cloture_saisie_note) REFERENCES public.users(id);


--
-- Name: operation_par_examen operation_par_examen_id_user_date_cloture_verification_en_tete_; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_id_user_date_cloture_verification_en_tete_ FOREIGN KEY (id_user_date_cloture_verification_en_tete) REFERENCES public.users(id);


--
-- Name: operation_par_examen operation_par_examen_id_user_date_cloture_verification_note_for; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_id_user_date_cloture_verification_note_for FOREIGN KEY (id_user_date_cloture_verification_note) REFERENCES public.users(id);


--
-- Name: operation_par_examen operation_par_examen_id_user_date_ouverture_saisie_en_tete_fore; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_id_user_date_ouverture_saisie_en_tete_fore FOREIGN KEY (id_user_date_ouverture_saisie_en_tete) REFERENCES public.users(id);


--
-- Name: operation_par_examen operation_par_examen_id_user_date_ouverture_saisie_note_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_id_user_date_ouverture_saisie_note_foreign FOREIGN KEY (id_user_date_ouverture_saisie_note) REFERENCES public.users(id);


--
-- Name: operation_par_examen operation_par_examen_id_user_date_ouverture_verification_en_tet; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_id_user_date_ouverture_verification_en_tet FOREIGN KEY (id_user_date_ouverture_verification_en_tete) REFERENCES public.users(id);


--
-- Name: operation_par_examen operation_par_examen_id_user_date_ouverture_verification_note_f; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_id_user_date_ouverture_verification_note_f FOREIGN KEY (id_user_date_ouverture_verification_note) REFERENCES public.users(id);


--
-- Name: operation_par_examen operation_par_examen_id_user_date_resultats_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_examen
    ADD CONSTRAINT operation_par_examen_id_user_date_resultats_foreign FOREIGN KEY (id_user_date_resultats) REFERENCES public.users(id);


--
-- Name: operation_par_import_resultat_paces operation_par_import_resultat_paces_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_import_resultat_paces
    ADD CONSTRAINT operation_par_import_resultat_paces_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: operation_par_import_resultat_paces operation_par_import_resultat_paces_id_parcours_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_import_resultat_paces
    ADD CONSTRAINT operation_par_import_resultat_paces_id_parcours_foreign FOREIGN KEY (id_parcours) REFERENCES public.parcours(id_parcours);


--
-- Name: operation_par_import_resultat_paces operation_par_import_resultat_paces_id_user_date_import_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_par_import_resultat_paces
    ADD CONSTRAINT operation_par_import_resultat_paces_id_user_date_import_foreign FOREIGN KEY (id_user_date_import) REFERENCES public.users(id);


--
-- Name: operation_sur_examen_par_au operation_sur_examen_par_au_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_sur_examen_par_au
    ADD CONSTRAINT operation_sur_examen_par_au_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: operation_sur_examen_par_au operation_sur_examen_par_au_id_user_date_deliberation_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_sur_examen_par_au
    ADD CONSTRAINT operation_sur_examen_par_au_id_user_date_deliberation_foreign FOREIGN KEY (id_user_date_deliberation) REFERENCES public.users(id);


--
-- Name: operation_sur_examen_par_au operation_sur_examen_par_au_id_user_date_liste_repechage_foreig; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_sur_examen_par_au
    ADD CONSTRAINT operation_sur_examen_par_au_id_user_date_liste_repechage_foreig FOREIGN KEY (id_user_date_liste_repechage) REFERENCES public.users(id);


--
-- Name: operation_sur_examen_par_au operation_sur_examen_par_au_id_user_date_resultat_avant_deliber; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_sur_examen_par_au
    ADD CONSTRAINT operation_sur_examen_par_au_id_user_date_resultat_avant_deliber FOREIGN KEY (id_user_date_resultat_avant_deliberation) REFERENCES public.users(id);


--
-- Name: operation_sur_examen_par_au operation_sur_examen_par_au_id_user_date_resultat_definitif_for; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.operation_sur_examen_par_au
    ADD CONSTRAINT operation_sur_examen_par_au_id_user_date_resultat_definitif_for FOREIGN KEY (id_user_date_resultat_definitif) REFERENCES public.users(id);


--
-- Name: parcours parcours_id_mention_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parcours
    ADD CONSTRAINT parcours_id_mention_foreign FOREIGN KEY (id_mention) REFERENCES public.mention(id_mention);


--
-- Name: parcours_niveau parcours_niveau_id_niveau_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parcours_niveau
    ADD CONSTRAINT parcours_niveau_id_niveau_foreign FOREIGN KEY (id_niveau) REFERENCES public.niveau(id_niveau);


--
-- Name: parcours_niveau parcours_niveau_id_parcours_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.parcours_niveau
    ADD CONSTRAINT parcours_niveau_id_parcours_foreign FOREIGN KEY (id_parcours) REFERENCES public.parcours(id_parcours);


--
-- Name: resultats_avant_deliberation resultats_avant_deliberation_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_deliberation
    ADD CONSTRAINT resultats_avant_deliberation_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: resultats_avant_deliberation resultats_avant_deliberation_id_element_constitutif_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_deliberation
    ADD CONSTRAINT resultats_avant_deliberation_id_element_constitutif_foreign FOREIGN KEY (id_element_constitutif) REFERENCES public.element_constitutif(id_element_constitutif);


--
-- Name: resultats_avant_deliberation resultats_avant_deliberation_id_etudiants_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_deliberation
    ADD CONSTRAINT resultats_avant_deliberation_id_etudiants_foreign FOREIGN KEY (id_etudiants) REFERENCES public.etudiants(id_etudiants);


--
-- Name: resultats_avant_deliberation resultats_avant_deliberation_id_examen_par_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_deliberation
    ADD CONSTRAINT resultats_avant_deliberation_id_examen_par_au_foreign FOREIGN KEY (id_examen_par_au) REFERENCES public.examen_par_au(id_examen_par_au);


--
-- Name: resultats_avant_deliberation resultats_avant_deliberation_id_niveau_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_deliberation
    ADD CONSTRAINT resultats_avant_deliberation_id_niveau_foreign FOREIGN KEY (id_niveau) REFERENCES public.niveau(id_niveau);


--
-- Name: resultats_avant_deliberation resultats_avant_deliberation_id_niveau_suivant_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_deliberation
    ADD CONSTRAINT resultats_avant_deliberation_id_niveau_suivant_foreign FOREIGN KEY (id_niveau_suivant) REFERENCES public.niveau(id_niveau);


--
-- Name: resultats_avant_deliberation resultats_avant_deliberation_id_parcours_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_deliberation
    ADD CONSTRAINT resultats_avant_deliberation_id_parcours_foreign FOREIGN KEY (id_parcours) REFERENCES public.parcours(id_parcours);


--
-- Name: resultats_avant_deliberation resultats_avant_deliberation_id_session_examen_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_deliberation
    ADD CONSTRAINT resultats_avant_deliberation_id_session_examen_foreign FOREIGN KEY (id_session_examen) REFERENCES public.session_examen(id_session_examen);


--
-- Name: resultats_avant_deliberation resultats_avant_deliberation_id_unite_enseignement_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_deliberation
    ADD CONSTRAINT resultats_avant_deliberation_id_unite_enseignement_foreign FOREIGN KEY (id_unite_enseignement) REFERENCES public.unite_enseignement(id_unite_enseignement);


--
-- Name: resultats_avant_repechage resultats_avant_repechage_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage
    ADD CONSTRAINT resultats_avant_repechage_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: resultats_avant_repechage resultats_avant_repechage_id_element_constitutif_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage
    ADD CONSTRAINT resultats_avant_repechage_id_element_constitutif_foreign FOREIGN KEY (id_element_constitutif) REFERENCES public.element_constitutif(id_element_constitutif);


--
-- Name: resultats_avant_repechage resultats_avant_repechage_id_etudiants_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage
    ADD CONSTRAINT resultats_avant_repechage_id_etudiants_foreign FOREIGN KEY (id_etudiants) REFERENCES public.etudiants(id_etudiants);


--
-- Name: resultats_avant_repechage resultats_avant_repechage_id_examen_par_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage
    ADD CONSTRAINT resultats_avant_repechage_id_examen_par_au_foreign FOREIGN KEY (id_examen_par_au) REFERENCES public.examen_par_au(id_examen_par_au);


--
-- Name: resultats_avant_repechage resultats_avant_repechage_id_niveau_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage
    ADD CONSTRAINT resultats_avant_repechage_id_niveau_foreign FOREIGN KEY (id_niveau) REFERENCES public.niveau(id_niveau);


--
-- Name: resultats_avant_repechage resultats_avant_repechage_id_note_eval_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage
    ADD CONSTRAINT resultats_avant_repechage_id_note_eval_foreign FOREIGN KEY (id_note_eval) REFERENCES public.note_eval(id_note_eval);


--
-- Name: resultats_avant_repechage resultats_avant_repechage_id_parcours_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage
    ADD CONSTRAINT resultats_avant_repechage_id_parcours_foreign FOREIGN KEY (id_parcours) REFERENCES public.parcours(id_parcours);


--
-- Name: resultats_avant_repechage resultats_avant_repechage_id_session_examen_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage
    ADD CONSTRAINT resultats_avant_repechage_id_session_examen_foreign FOREIGN KEY (id_session_examen) REFERENCES public.session_examen(id_session_examen);


--
-- Name: resultats_avant_repechage resultats_avant_repechage_id_ue_ec_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage
    ADD CONSTRAINT resultats_avant_repechage_id_ue_ec_foreign FOREIGN KEY (id_ue_ec) REFERENCES public.ue_ec_parcours_niveau_au(id_ue_ec);


--
-- Name: resultats_avant_repechage resultats_avant_repechage_id_unite_enseignement_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_avant_repechage
    ADD CONSTRAINT resultats_avant_repechage_id_unite_enseignement_foreign FOREIGN KEY (id_unite_enseignement) REFERENCES public.unite_enseignement(id_unite_enseignement);


--
-- Name: resultats_definitifs resultats_definitifs_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_definitifs
    ADD CONSTRAINT resultats_definitifs_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: resultats_definitifs resultats_definitifs_id_ec_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_definitifs
    ADD CONSTRAINT resultats_definitifs_id_ec_foreign FOREIGN KEY (id_ec) REFERENCES public.element_constitutif(id_element_constitutif);


--
-- Name: resultats_definitifs resultats_definitifs_id_etudiants_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_definitifs
    ADD CONSTRAINT resultats_definitifs_id_etudiants_foreign FOREIGN KEY (id_etudiants) REFERENCES public.etudiants(id_etudiants);


--
-- Name: resultats_definitifs resultats_definitifs_id_examen_par_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_definitifs
    ADD CONSTRAINT resultats_definitifs_id_examen_par_au_foreign FOREIGN KEY (id_examen_par_au) REFERENCES public.examen_par_au(id_examen_par_au);


--
-- Name: resultats_definitifs resultats_definitifs_id_niveau_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_definitifs
    ADD CONSTRAINT resultats_definitifs_id_niveau_foreign FOREIGN KEY (id_niveau) REFERENCES public.niveau(id_niveau);


--
-- Name: resultats_definitifs resultats_definitifs_id_niveau_suivant_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_definitifs
    ADD CONSTRAINT resultats_definitifs_id_niveau_suivant_foreign FOREIGN KEY (id_niveau_suivant) REFERENCES public.niveau(id_niveau);


--
-- Name: resultats_definitifs resultats_definitifs_id_parcours_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_definitifs
    ADD CONSTRAINT resultats_definitifs_id_parcours_foreign FOREIGN KEY (id_parcours) REFERENCES public.parcours(id_parcours);


--
-- Name: resultats_definitifs resultats_definitifs_id_session_examen_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_definitifs
    ADD CONSTRAINT resultats_definitifs_id_session_examen_foreign FOREIGN KEY (id_session_examen) REFERENCES public.session_examen(id_session_examen);


--
-- Name: resultats_definitifs resultats_definitifs_id_ue_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.resultats_definitifs
    ADD CONSTRAINT resultats_definitifs_id_ue_foreign FOREIGN KEY (id_ue) REFERENCES public.unite_enseignement(id_unite_enseignement);


--
-- Name: selectionnes selectionnes_id_agent_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.selectionnes
    ADD CONSTRAINT selectionnes_id_agent_foreign FOREIGN KEY (id_agent) REFERENCES public.users(id);


--
-- Name: selectionnes selectionnes_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.selectionnes
    ADD CONSTRAINT selectionnes_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: selectionnes selectionnes_id_parcours_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.selectionnes
    ADD CONSTRAINT selectionnes_id_parcours_foreign FOREIGN KEY (id_parcours) REFERENCES public.parcours(id_parcours);


--
-- Name: transferts_autorises transferts_autorises_id_niveau_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.transferts_autorises
    ADD CONSTRAINT transferts_autorises_id_niveau_foreign FOREIGN KEY (id_niveau) REFERENCES public.niveau(id_niveau);


--
-- Name: transferts_autorises transferts_autorises_id_parcours_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.transferts_autorises
    ADD CONSTRAINT transferts_autorises_id_parcours_foreign FOREIGN KEY (id_parcours) REFERENCES public.parcours(id_parcours);


--
-- Name: ue_ec_parcours_niveau_au ue_ec_parcours_niveau_au_id_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ue_ec_parcours_niveau_au
    ADD CONSTRAINT ue_ec_parcours_niveau_au_id_au_foreign FOREIGN KEY (id_au) REFERENCES public.au(id_au);


--
-- Name: ue_ec_parcours_niveau_au ue_ec_parcours_niveau_au_id_element_constitutif_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ue_ec_parcours_niveau_au
    ADD CONSTRAINT ue_ec_parcours_niveau_au_id_element_constitutif_foreign FOREIGN KEY (id_element_constitutif) REFERENCES public.element_constitutif(id_element_constitutif);


--
-- Name: ue_ec_parcours_niveau_au ue_ec_parcours_niveau_au_id_examen_par_au_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ue_ec_parcours_niveau_au
    ADD CONSTRAINT ue_ec_parcours_niveau_au_id_examen_par_au_foreign FOREIGN KEY (id_examen_par_au) REFERENCES public.examen_par_au(id_examen_par_au);


--
-- Name: ue_ec_parcours_niveau_au ue_ec_parcours_niveau_au_id_niveau_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ue_ec_parcours_niveau_au
    ADD CONSTRAINT ue_ec_parcours_niveau_au_id_niveau_foreign FOREIGN KEY (id_niveau) REFERENCES public.niveau(id_niveau);


--
-- Name: ue_ec_parcours_niveau_au ue_ec_parcours_niveau_au_id_parcours_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ue_ec_parcours_niveau_au
    ADD CONSTRAINT ue_ec_parcours_niveau_au_id_parcours_foreign FOREIGN KEY (id_parcours) REFERENCES public.parcours(id_parcours);


--
-- Name: ue_ec_parcours_niveau_au ue_ec_parcours_niveau_au_id_unite_enseignement_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ue_ec_parcours_niveau_au
    ADD CONSTRAINT ue_ec_parcours_niveau_au_id_unite_enseignement_foreign FOREIGN KEY (id_unite_enseignement) REFERENCES public.unite_enseignement(id_unite_enseignement);


--
-- Name: users users_id_role_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_id_role_foreign FOREIGN KEY (id_role) REFERENCES public.role(id);


--
-- Name: v_inscrits2; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: postgres
--

REFRESH MATERIALIZED VIEW public.v_inscrits2;


--
-- Name: v_resultats_avant_repechage_complet; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: postgres
--

REFRESH MATERIALIZED VIEW public.v_resultats_avant_repechage_complet;


--
-- Name: v_liste_repechage; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: postgres
--

REFRESH MATERIALIZED VIEW public.v_liste_repechage;


--
-- Name: v_liste_repechage_affichage; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: postgres
--

REFRESH MATERIALIZED VIEW public.v_liste_repechage_affichage;


--
-- Name: v_liste_ue_ec_avec_nbr_inscrits; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: postgres
--

REFRESH MATERIALIZED VIEW public.v_liste_ue_ec_avec_nbr_inscrits;


--
-- Name: v_resultats_avant_deliberation; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: postgres
--

REFRESH MATERIALIZED VIEW public.v_resultats_avant_deliberation;


--
-- PostgreSQL database dump complete
--

