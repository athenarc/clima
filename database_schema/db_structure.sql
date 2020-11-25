--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.14
-- Dumped by pg_dump version 9.5.14

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: fuzzystrmatch; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS fuzzystrmatch WITH SCHEMA public;


--
-- Name: EXTENSION fuzzystrmatch; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION fuzzystrmatch IS 'determine similarities and distance between strings';


--
-- Name: maturity_type; Type: TYPE; Schema: public; Owner: rac_management
--

CREATE TYPE public.maturity_type AS ENUM (
    'developing',
    'testing',
    'production'
);


ALTER TYPE public.maturity_type OWNER TO rac_management;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: auth_assignment; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.auth_assignment (
    item_name character varying(64) NOT NULL,
    user_id integer NOT NULL,
    created_at integer
);


ALTER TABLE public.auth_assignment OWNER TO rac_management;

--
-- Name: auth_item; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.auth_item (
    name character varying(64) NOT NULL,
    type integer NOT NULL,
    description text,
    rule_name character varying(64),
    data text,
    created_at integer,
    updated_at integer,
    group_code character varying(64)
);


ALTER TABLE public.auth_item OWNER TO rac_management;

--
-- Name: auth_item_child; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.auth_item_child (
    parent character varying(64) NOT NULL,
    child character varying(64) NOT NULL
);


ALTER TABLE public.auth_item_child OWNER TO rac_management;

--
-- Name: auth_item_group; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.auth_item_group (
    code character varying(64) NOT NULL,
    name character varying(255) NOT NULL,
    created_at integer,
    updated_at integer
);


ALTER TABLE public.auth_item_group OWNER TO rac_management;

--
-- Name: auth_rule; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.auth_rule (
    name character varying(64) NOT NULL,
    data text,
    created_at integer,
    updated_at integer
);


ALTER TABLE public.auth_rule OWNER TO rac_management;

--
-- Name: cold_storage_autoaccept; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.cold_storage_autoaccept (
    storage double precision,
    user_type character varying(15)
);


ALTER TABLE public.cold_storage_autoaccept OWNER TO rac_management;

--
-- Name: cold_storage_limits; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.cold_storage_limits (
    storage bigint,
    user_type character varying(15),
    duration integer
);


ALTER TABLE public.cold_storage_limits OWNER TO rac_management;

--
-- Name: cold_storage_request; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.cold_storage_request (
    id bigint NOT NULL,
    request_id bigint,
    storage double precision,
    description text,
    additional_resources text
);


ALTER TABLE public.cold_storage_request OWNER TO rac_management;

--
-- Name: cold_storage_request_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.cold_storage_request_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.cold_storage_request_id_seq OWNER TO rac_management;

--
-- Name: cold_storage_request_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.cold_storage_request_id_seq OWNED BY public.cold_storage_request.id;


--
-- Name: configuration; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.configuration (
    reviewer_num integer,
    home_page integer,
    privacy_page integer,
    help_page integer
);


ALTER TABLE public.configuration OWNER TO rac_management;

--
-- Name: email; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.email (
    id integer NOT NULL,
    recipient_ids integer[],
    type text,
    sent_at timestamp without time zone,
    message text,
    project_id integer
);


ALTER TABLE public.email OWNER TO rac_management;

--
-- Name: email_events; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.email_events (
    id integer NOT NULL,
    user_id integer,
    user_creation boolean,
    new_project boolean,
    project_decision boolean,
    new_ticket boolean,
    expires_30 boolean,
    expires_15 boolean
);


ALTER TABLE public.email_events OWNER TO rac_management;

--
-- Name: email_events_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.email_events_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.email_events_id_seq OWNER TO rac_management;

--
-- Name: email_events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.email_events_id_seq OWNED BY public.email_events.id;


--
-- Name: email_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.email_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.email_id_seq OWNER TO rac_management;

--
-- Name: email_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.email_id_seq OWNED BY public.email.id;


--
-- Name: migration; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.migration (
    version character varying(180) NOT NULL,
    apply_time integer
);


ALTER TABLE public.migration OWNER TO rac_management;

--
-- Name: notification; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.notification (
    id integer NOT NULL,
    recipient_id integer NOT NULL,
    message text,
    seen boolean DEFAULT false,
    type integer,
    created_at timestamp without time zone,
    read_at timestamp without time zone,
    url text
);


ALTER TABLE public.notification OWNER TO rac_management;

--
-- Name: notification_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.notification_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.notification_id_seq OWNER TO rac_management;

--
-- Name: notification_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.notification_id_seq OWNED BY public.notification.id;


--
-- Name: notification_recipient_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.notification_recipient_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.notification_recipient_id_seq OWNER TO rac_management;

--
-- Name: notification_recipient_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.notification_recipient_id_seq OWNED BY public.notification.recipient_id;


--
-- Name: ondemand_autoaccept; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.ondemand_autoaccept (
    num_of_jobs integer,
    time_per_job double precision,
    cores integer,
    ram double precision,
    storage double precision,
    user_type character varying(15)
);


ALTER TABLE public.ondemand_autoaccept OWNER TO rac_management;

--
-- Name: ondemand_request; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.ondemand_request (
    id bigint NOT NULL,
    request_id bigint,
    description text,
    maturity public.maturity_type,
    analysis_type character varying(200),
    containerized boolean,
    storage double precision,
    num_of_jobs integer,
    time_per_job double precision,
    ram double precision,
    cores integer,
    additional_resources text
);


ALTER TABLE public.ondemand_request OWNER TO rac_management;

--
-- Name: ondemand_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.ondemand_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ondemand_id_seq OWNER TO rac_management;

--
-- Name: ondemand_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.ondemand_id_seq OWNED BY public.ondemand_request.id;


--
-- Name: ondemand_limits; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.ondemand_limits (
    num_of_jobs integer,
    time_per_job double precision,
    cores integer,
    ram double precision,
    storage double precision,
    user_type character varying(15),
    duration integer
);


ALTER TABLE public.ondemand_limits OWNER TO rac_management;

--
-- Name: pages; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.pages (
    id integer NOT NULL,
    title text,
    content text
);


ALTER TABLE public.pages OWNER TO rac_management;

--
-- Name: pages_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.pages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.pages_id_seq OWNER TO rac_management;

--
-- Name: pages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.pages_id_seq OWNED BY public.pages.id;


--
-- Name: project; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.project (
    id bigint NOT NULL,
    name character varying(200),
    status smallint DEFAULT 0,
    latest_project_request_id bigint,
    pending_request_id bigint,
    project_type smallint
);


ALTER TABLE public.project OWNER TO rac_management;

--
-- Name: project_request; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.project_request (
    id bigint NOT NULL,
    name character varying(200),
    duration integer,
    user_num integer,
    user_list integer[],
    backup_services boolean,
    viewed boolean DEFAULT false,
    status smallint DEFAULT 0,
    submitted_by bigint,
    submission_date timestamp without time zone,
    assigned_to bigint,
    project_type smallint,
    project_id bigint,
    approval_date timestamp without time zone,
    approved_by integer,
    deletion_date timestamp without time zone,
    end_date date,
    additional_resources text
);


ALTER TABLE public.project_request OWNER TO rac_management;

--
-- Name: project_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.project_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.project_id_seq OWNER TO rac_management;

--
-- Name: project_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.project_id_seq OWNED BY public.project_request.id;


--
-- Name: project_id_seq1; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.project_id_seq1
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.project_id_seq1 OWNER TO rac_management;

--
-- Name: project_id_seq1; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.project_id_seq1 OWNED BY public.project.id;


--
-- Name: service_autoaccept; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.service_autoaccept (
    vms integer,
    cores integer,
    ips integer,
    ram double precision,
    storage double precision,
    user_type character varying(15)
);


ALTER TABLE public.service_autoaccept OWNER TO rac_management;

--
-- Name: service_limits; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.service_limits (
    vms integer,
    cores integer,
    ips integer,
    ram double precision,
    storage double precision,
    user_type character varying(15),
    duration integer
);


ALTER TABLE public.service_limits OWNER TO rac_management;

--
-- Name: service_request; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.service_request (
    id bigint NOT NULL,
    name character varying(200),
    version character varying(50),
    description text,
    url text,
    num_of_vms smallint,
    num_of_cores smallint,
    num_of_ips smallint,
    ram double precision,
    storage double precision,
    request_id bigint NOT NULL,
    trl smallint,
    vm_flavour text,
    disk integer,
    additional_resources text
);


ALTER TABLE public.service_request OWNER TO rac_management;

--
-- Name: services_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.services_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.services_id_seq OWNER TO rac_management;

--
-- Name: services_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.services_id_seq OWNED BY public.service_request.id;


--
-- Name: services_project_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.services_project_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.services_project_id_seq OWNER TO rac_management;

--
-- Name: services_project_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.services_project_id_seq OWNED BY public.service_request.request_id;


--
-- Name: smtp; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.smtp (
    id integer NOT NULL,
    encryption text,
    host text,
    username text,
    port text,
    password text
);


ALTER TABLE public.smtp OWNER TO rac_management;

--
-- Name: smtp_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.smtp_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.smtp_id_seq OWNER TO rac_management;

--
-- Name: smtp_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.smtp_id_seq OWNED BY public.smtp.id;


--
-- Name: ticket_body; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.ticket_body (
    id integer NOT NULL,
    id_head integer NOT NULL,
    name_user character varying(255),
    text text,
    client integer DEFAULT 0,
    date timestamp(0) without time zone
);


ALTER TABLE public.ticket_body OWNER TO rac_management;

--
-- Name: ticket_body_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.ticket_body_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ticket_body_id_seq OWNER TO rac_management;

--
-- Name: ticket_body_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.ticket_body_id_seq OWNED BY public.ticket_body.id;


--
-- Name: ticket_file; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.ticket_file (
    id integer NOT NULL,
    id_body integer NOT NULL,
    "fileName" character varying(255) NOT NULL,
    document_name character varying(255)
);


ALTER TABLE public.ticket_file OWNER TO rac_management;

--
-- Name: ticket_file_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.ticket_file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ticket_file_id_seq OWNER TO rac_management;

--
-- Name: ticket_file_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.ticket_file_id_seq OWNED BY public.ticket_file.id;


--
-- Name: ticket_head; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.ticket_head (
    id integer NOT NULL,
    user_id integer NOT NULL,
    department character varying(255),
    topic character varying(255),
    status integer DEFAULT 0,
    date_update timestamp(0) without time zone DEFAULT NULL::timestamp without time zone,
    page text
);


ALTER TABLE public.ticket_head OWNER TO rac_management;

--
-- Name: ticket_head_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.ticket_head_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ticket_head_id_seq OWNER TO rac_management;

--
-- Name: ticket_head_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.ticket_head_id_seq OWNED BY public.ticket_head.id;


--
-- Name: user; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public."user" (
    id integer NOT NULL,
    username character varying(255) NOT NULL,
    auth_key character varying(32) NOT NULL,
    password_hash character varying(255) NOT NULL,
    confirmation_token character varying(255),
    status integer DEFAULT 1 NOT NULL,
    superadmin smallint DEFAULT 0,
    created_at integer NOT NULL,
    updated_at integer NOT NULL,
    registration_ip character varying(15),
    bind_to_ip character varying(255),
    email character varying(128),
    email_confirmed smallint DEFAULT 0 NOT NULL,
    name character varying(100),
    surname character varying(100)
);


ALTER TABLE public."user" OWNER TO rac_management;

--
-- Name: user_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_id_seq OWNER TO rac_management;

--
-- Name: user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.user_id_seq OWNED BY public."user".id;


--
-- Name: user_visit_log; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.user_visit_log (
    id integer NOT NULL,
    token character varying(255) NOT NULL,
    ip character varying(15) NOT NULL,
    language character(2) NOT NULL,
    user_agent character varying(255) NOT NULL,
    user_id integer,
    visit_time integer NOT NULL,
    browser character varying(30),
    os character varying(20)
);


ALTER TABLE public.user_visit_log OWNER TO rac_management;

--
-- Name: user_visit_log_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.user_visit_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.user_visit_log_id_seq OWNER TO rac_management;

--
-- Name: user_visit_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.user_visit_log_id_seq OWNED BY public.user_visit_log.id;


--
-- Name: vm; Type: TABLE; Schema: public; Owner: rac_management
--

CREATE TABLE public.vm (
    id bigint NOT NULL,
    ip_address character varying(100),
    ip_id text,
    vm_id text,
    public_key text,
    image_id text,
    image_name character varying(100),
    request_id integer NOT NULL,
    active boolean,
    keypair_name character varying(255),
    created_by integer,
    deleted_by integer,
    volume_id text,
    created_at timestamp without time zone,
    deleted_at timestamp without time zone,
    do_not_delete_disk boolean DEFAULT false,
    windows_unique_id text,
    read_win_password boolean DEFAULT false,
    project_id bigint
);


ALTER TABLE public.vm OWNER TO rac_management;

--
-- Name: vm_id_seq; Type: SEQUENCE; Schema: public; Owner: rac_management
--

CREATE SEQUENCE public.vm_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.vm_id_seq OWNER TO rac_management;

--
-- Name: vm_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: rac_management
--

ALTER SEQUENCE public.vm_id_seq OWNED BY public.vm.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.cold_storage_request ALTER COLUMN id SET DEFAULT nextval('public.cold_storage_request_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.email ALTER COLUMN id SET DEFAULT nextval('public.email_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.email_events ALTER COLUMN id SET DEFAULT nextval('public.email_events_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.notification ALTER COLUMN id SET DEFAULT nextval('public.notification_id_seq'::regclass);


--
-- Name: recipient_id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.notification ALTER COLUMN recipient_id SET DEFAULT nextval('public.notification_recipient_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.ondemand_request ALTER COLUMN id SET DEFAULT nextval('public.ondemand_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.pages ALTER COLUMN id SET DEFAULT nextval('public.pages_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.project ALTER COLUMN id SET DEFAULT nextval('public.project_id_seq1'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.project_request ALTER COLUMN id SET DEFAULT nextval('public.project_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.service_request ALTER COLUMN id SET DEFAULT nextval('public.services_id_seq'::regclass);


--
-- Name: request_id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.service_request ALTER COLUMN request_id SET DEFAULT nextval('public.services_project_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.smtp ALTER COLUMN id SET DEFAULT nextval('public.smtp_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.ticket_body ALTER COLUMN id SET DEFAULT nextval('public.ticket_body_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.ticket_file ALTER COLUMN id SET DEFAULT nextval('public.ticket_file_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.ticket_head ALTER COLUMN id SET DEFAULT nextval('public.ticket_head_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public."user" ALTER COLUMN id SET DEFAULT nextval('public.user_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.user_visit_log ALTER COLUMN id SET DEFAULT nextval('public.user_visit_log_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.vm ALTER COLUMN id SET DEFAULT nextval('public.vm_id_seq'::regclass);


--
-- Name: auth_assignment_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.auth_assignment
    ADD CONSTRAINT auth_assignment_pkey PRIMARY KEY (item_name, user_id);


--
-- Name: auth_item_child_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.auth_item_child
    ADD CONSTRAINT auth_item_child_pkey PRIMARY KEY (parent, child);


--
-- Name: auth_item_group_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.auth_item_group
    ADD CONSTRAINT auth_item_group_pkey PRIMARY KEY (code);


--
-- Name: auth_item_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.auth_item
    ADD CONSTRAINT auth_item_pkey PRIMARY KEY (name);


--
-- Name: auth_rule_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.auth_rule
    ADD CONSTRAINT auth_rule_pkey PRIMARY KEY (name);


--
-- Name: cold_storage_request_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.cold_storage_request
    ADD CONSTRAINT cold_storage_request_pkey PRIMARY KEY (id);


--
-- Name: email_events_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.email_events
    ADD CONSTRAINT email_events_pkey PRIMARY KEY (id);


--
-- Name: email_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.email
    ADD CONSTRAINT email_pkey PRIMARY KEY (id);


--
-- Name: migration_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.migration
    ADD CONSTRAINT migration_pkey PRIMARY KEY (version);


--
-- Name: notification_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.notification
    ADD CONSTRAINT notification_pkey PRIMARY KEY (id);


--
-- Name: ondemand_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.ondemand_request
    ADD CONSTRAINT ondemand_pkey PRIMARY KEY (id);


--
-- Name: pages_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.pages
    ADD CONSTRAINT pages_pkey PRIMARY KEY (id);


--
-- Name: project_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.project_request
    ADD CONSTRAINT project_pkey PRIMARY KEY (id);


--
-- Name: project_pkey1; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.project
    ADD CONSTRAINT project_pkey1 PRIMARY KEY (id);


--
-- Name: services_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.service_request
    ADD CONSTRAINT services_pkey PRIMARY KEY (id);


--
-- Name: smtp_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.smtp
    ADD CONSTRAINT smtp_pkey PRIMARY KEY (id);


--
-- Name: ticket_body_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.ticket_body
    ADD CONSTRAINT ticket_body_pkey PRIMARY KEY (id);


--
-- Name: ticket_file_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.ticket_file
    ADD CONSTRAINT ticket_file_pkey PRIMARY KEY (id);


--
-- Name: ticket_head_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.ticket_head
    ADD CONSTRAINT ticket_head_pkey PRIMARY KEY (id);


--
-- Name: user_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public."user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);


--
-- Name: user_visit_log_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.user_visit_log
    ADD CONSTRAINT user_visit_log_pkey PRIMARY KEY (id);


--
-- Name: vm_pkey; Type: CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.vm
    ADD CONSTRAINT vm_pkey PRIMARY KEY (id);


--
-- Name: i_id_body; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX i_id_body ON public.ticket_file USING btree (id_body);


--
-- Name: i_ticket_body; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX i_ticket_body ON public.ticket_body USING btree (id_head);


--
-- Name: i_ticket_head; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX i_ticket_head ON public.ticket_head USING btree (user_id);


--
-- Name: idx-auth_item-type; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX "idx-auth_item-type" ON public.auth_item USING btree (type);


--
-- Name: idx_ondemand_project_id; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX idx_ondemand_project_id ON public.ondemand_request USING btree (request_id);


--
-- Name: idx_service_project_id; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX idx_service_project_id ON public.service_request USING btree (request_id);


--
-- Name: project_pending_request_id_idx; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX project_pending_request_id_idx ON public.project USING btree (pending_request_id);


--
-- Name: project_request_approval_date_idx; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX project_request_approval_date_idx ON public.project_request USING btree (approval_date);


--
-- Name: project_request_name_idx; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX project_request_name_idx ON public.project_request USING btree (name);


--
-- Name: project_request_submission_date_idx; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX project_request_submission_date_idx ON public.project_request USING btree (submission_date);


--
-- Name: vm_project_id_idx; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX vm_project_id_idx ON public.vm USING btree (project_id);


--
-- Name: vm_request_idx; Type: INDEX; Schema: public; Owner: rac_management
--

CREATE INDEX vm_request_idx ON public.vm USING btree (request_id);


--
-- Name: auth_assignment_item_name_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.auth_assignment
    ADD CONSTRAINT auth_assignment_item_name_fkey FOREIGN KEY (item_name) REFERENCES public.auth_item(name) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: auth_assignment_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.auth_assignment
    ADD CONSTRAINT auth_assignment_user_id_fkey FOREIGN KEY (user_id) REFERENCES public."user"(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: auth_item_rule_name_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.auth_item
    ADD CONSTRAINT auth_item_rule_name_fkey FOREIGN KEY (rule_name) REFERENCES public.auth_rule(name) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: fk_auth_item_group_code; Type: FK CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.auth_item
    ADD CONSTRAINT fk_auth_item_group_code FOREIGN KEY (group_code) REFERENCES public.auth_item_group(code) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: fk_id_body; Type: FK CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.ticket_file
    ADD CONSTRAINT fk_id_body FOREIGN KEY (id_body) REFERENCES public.ticket_body(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: fk_ticket_body; Type: FK CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.ticket_body
    ADD CONSTRAINT fk_ticket_body FOREIGN KEY (id_head) REFERENCES public.ticket_head(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: fk_ticket_head; Type: FK CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.ticket_head
    ADD CONSTRAINT fk_ticket_head FOREIGN KEY (user_id) REFERENCES public."user"(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: user_visit_log_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: rac_management
--

ALTER TABLE ONLY public.user_visit_log
    ADD CONSTRAINT user_visit_log_user_id_fkey FOREIGN KEY (user_id) REFERENCES public."user"(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

