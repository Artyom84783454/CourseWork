--
-- PostgreSQL database dump
--

-- Dumped from database version 16.3
-- Dumped by pg_dump version 16.3

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
-- Name: generate_random_ip(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.generate_random_ip() RETURNS character varying
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN concat_ws('.', 
                     trunc(random() * 255)::int, 
                     trunc(random() * 255)::int, 
                     trunc(random() * 255)::int, 
                     trunc(random() * 255)::int);
END;
$$;


ALTER FUNCTION public.generate_random_ip() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: activity_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.activity_log (
    log_id integer NOT NULL,
    user_id integer,
    action_type character varying(50),
    action_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    description text,
    CONSTRAINT activity_log_action_type_check CHECK (((action_type)::text = ANY ((ARRAY['login'::character varying, 'logout'::character varying, 'change_tariff'::character varying, 'payment'::character varying, 'update_profile'::character varying])::text[])))
);


ALTER TABLE public.activity_log OWNER TO postgres;

--
-- Name: activity_log_log_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.activity_log_log_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.activity_log_log_id_seq OWNER TO postgres;

--
-- Name: activity_log_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.activity_log_log_id_seq OWNED BY public.activity_log.log_id;


--
-- Name: connections; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.connections (
    connection_id integer NOT NULL,
    user_id integer,
    tariff_id integer,
    connection_start timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    connection_end timestamp without time zone,
    ip_address character varying(45)
);


ALTER TABLE public.connections OWNER TO postgres;

--
-- Name: connections_connection_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.connections_connection_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.connections_connection_id_seq OWNER TO postgres;

--
-- Name: connections_connection_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.connections_connection_id_seq OWNED BY public.connections.connection_id;


--
-- Name: payments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payments (
    payment_id integer NOT NULL,
    user_id integer,
    amount numeric(10,2) NOT NULL,
    payment_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    payment_method character varying(50),
    status character varying(20) DEFAULT 'pending'::character varying,
    CONSTRAINT payments_payment_method_check CHECK (((payment_method)::text = ANY ((ARRAY['credit_card'::character varying, 'paypal'::character varying, 'bank_transfer'::character varying])::text[]))),
    CONSTRAINT payments_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'completed'::character varying, 'failed'::character varying])::text[])))
);


ALTER TABLE public.payments OWNER TO postgres;

--
-- Name: payments_payment_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payments_payment_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.payments_payment_id_seq OWNER TO postgres;

--
-- Name: payments_payment_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payments_payment_id_seq OWNED BY public.payments.payment_id;


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    permission_id integer NOT NULL,
    role character varying(20),
    permission_name character varying(50),
    CONSTRAINT permissions_role_check CHECK (((role)::text = ANY ((ARRAY['user'::character varying, 'admin'::character varying, 'support'::character varying])::text[])))
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- Name: permissions_permission_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permissions_permission_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_permission_id_seq OWNER TO postgres;

--
-- Name: permissions_permission_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_permission_id_seq OWNED BY public.permissions.permission_id;


--
-- Name: tariffs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tariffs (
    tariff_id integer NOT NULL,
    name character varying(100) NOT NULL,
    speed integer NOT NULL,
    price numeric(10,2) NOT NULL,
    description text,
    valid_from date,
    valid_to date
);


ALTER TABLE public.tariffs OWNER TO postgres;

--
-- Name: tariffs_tariff_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tariffs_tariff_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tariffs_tariff_id_seq OWNER TO postgres;

--
-- Name: tariffs_tariff_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tariffs_tariff_id_seq OWNED BY public.tariffs.tariff_id;


--
-- Name: user_profiles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_profiles (
    profile_id integer NOT NULL,
    user_id integer,
    first_name character varying(50),
    last_name character varying(50),
    birthdate date,
    gender character varying(10)
);


ALTER TABLE public.user_profiles OWNER TO postgres;

--
-- Name: user_profiles_profile_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_profiles_profile_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_profiles_profile_id_seq OWNER TO postgres;

--
-- Name: user_profiles_profile_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_profiles_profile_id_seq OWNED BY public.user_profiles.profile_id;


--
-- Name: user_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.user_roles (
    user_role_id integer NOT NULL,
    user_id integer,
    role character varying(20),
    CONSTRAINT user_roles_role_check CHECK (((role)::text = ANY ((ARRAY['user'::character varying, 'admin'::character varying, 'support'::character varying])::text[])))
);


ALTER TABLE public.user_roles OWNER TO postgres;

--
-- Name: user_roles_user_role_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.user_roles_user_role_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.user_roles_user_role_id_seq OWNER TO postgres;

--
-- Name: user_roles_user_role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.user_roles_user_role_id_seq OWNED BY public.user_roles.user_role_id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    user_id integer NOT NULL,
    username character varying(50) NOT NULL,
    password_hash character varying(255) NOT NULL,
    email character varying(100) NOT NULL,
    phone_number character varying(20),
    registration_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    address character varying(255),
    role character varying(20) DEFAULT 'user'::character varying,
    status character varying(20) DEFAULT 'online'::character varying NOT NULL,
    balance numeric(10,2) DEFAULT 0.00,
    CONSTRAINT status_check CHECK (((status)::text = ANY ((ARRAY['online'::character varying, 'offline'::character varying])::text[]))),
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['user'::character varying, 'admin'::character varying, 'support'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_user_id_seq OWNER TO postgres;

--
-- Name: users_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_user_id_seq OWNED BY public.users.user_id;


--
-- Name: activity_log log_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_log ALTER COLUMN log_id SET DEFAULT nextval('public.activity_log_log_id_seq'::regclass);


--
-- Name: connections connection_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.connections ALTER COLUMN connection_id SET DEFAULT nextval('public.connections_connection_id_seq'::regclass);


--
-- Name: payments payment_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments ALTER COLUMN payment_id SET DEFAULT nextval('public.payments_payment_id_seq'::regclass);


--
-- Name: permissions permission_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN permission_id SET DEFAULT nextval('public.permissions_permission_id_seq'::regclass);


--
-- Name: tariffs tariff_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tariffs ALTER COLUMN tariff_id SET DEFAULT nextval('public.tariffs_tariff_id_seq'::regclass);


--
-- Name: user_profiles profile_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_profiles ALTER COLUMN profile_id SET DEFAULT nextval('public.user_profiles_profile_id_seq'::regclass);


--
-- Name: user_roles user_role_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_roles ALTER COLUMN user_role_id SET DEFAULT nextval('public.user_roles_user_role_id_seq'::regclass);


--
-- Name: users user_id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN user_id SET DEFAULT nextval('public.users_user_id_seq'::regclass);


--
-- Data for Name: activity_log; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.activity_log (log_id, user_id, action_type, action_date, description) FROM stdin;
\.


--
-- Data for Name: connections; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.connections (connection_id, user_id, tariff_id, connection_start, connection_end, ip_address) FROM stdin;
16	3	2	2024-06-12 12:00:00	\N	32.123.205.128
17	4	3	2024-06-12 12:00:00	\N	186.216.125.179
18	21	4	2024-06-12 12:00:00	\N	50.203.116.250
19	22	5	2024-06-12 12:00:00	\N	213.141.226.111
20	23	6	2024-06-12 12:00:00	\N	72.150.251.189
21	8	6	2024-06-12 12:00:00	\N	15.122.222.128
15	1	2	2024-06-29 15:39:21	2025-02-08 19:00:40	\N
\.


--
-- Data for Name: payments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payments (payment_id, user_id, amount, payment_date, payment_method, status) FROM stdin;
1	1	400.00	2024-06-13 14:24:23	credit_card	pending
2	1	300.00	2024-06-13 15:46:44	credit_card	pending
3	1	300.00	2024-06-13 15:57:14	credit_card	completed
4	1	200.00	2024-06-13 15:57:47	credit_card	completed
5	1	600.00	2024-06-13 16:09:28	credit_card	completed
6	3	300.00	2024-06-13 16:17:15	credit_card	completed
7	3	300.00	2024-06-13 16:17:50	credit_card	completed
8	1	300.00	2024-06-13 16:44:05	credit_card	completed
9	1	300.00	2024-06-13 16:58:29	credit_card	completed
10	1	300.00	2024-06-13 16:58:59	credit_card	completed
11	1	300.00	2024-06-13 17:36:45	credit_card	completed
12	1	300.00	2024-06-13 17:44:27	credit_card	completed
13	1	300.00	2024-06-16 13:07:10	credit_card	completed
14	1	300.00	2024-06-28 12:29:40	credit_card	completed
15	26	300.00	2024-06-29 12:35:32	credit_card	completed
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permissions (permission_id, role, permission_name) FROM stdin;
\.


--
-- Data for Name: tariffs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tariffs (tariff_id, name, speed, price, description, valid_from, valid_to) FROM stdin;
2	Домашній 100	100	200.00	Інтернет зі швидкістю до 100 Мбіт/с. Ідеально підходить для активних користувачів.	2024-01-01	\N
3	Домашній 200	200	300.00	Інтернет зі швидкістю до 200 Мбіт/с. Чудовий вибір для великих сімей.	2024-01-01	\N
4	Преміум 300	300	400.00	Інтернет зі швидкістю до 300 Мбіт/с. Висока швидкість та пріоритетне обслуговування.	2024-01-01	\N
5	Преміум 500	500	600.00	Інтернет зі швидкістю до 500 Мбіт/с. Для найвимогливіших користувачів.	2024-01-01	\N
6	Акція: Літо 2024	100	150.00	Спеціальна пропозиція на літо 2024 року. Інтернет зі швидкістю до 100 Мбіт/с.	2024-06-01	2024-08-31
1	Домашній 50	50	100.00	Інтернет зі швидкістю до 50 Мбіт/с. Безперебійне з'єднання та стабільна швидкість.	2024-01-01	\N
\.


--
-- Data for Name: user_profiles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_profiles (profile_id, user_id, first_name, last_name, birthdate, gender) FROM stdin;
2	3	Наталія	Шевченко	1990-06-12	жінка
3	4	Іван 	Петренко	1985-04-11	чоловік
4	8	Анна	Смирнова	2003-03-12	жінка
5	21	Дмитро	Мельник	1999-05-12	male
6	22	Admin	Admin	1999-01-01	Чоловік
7	23	Вікторія	Богданова	1984-05-10	Жінка
8	25	Вікторія	Смирнова	2003-05-27	female
9	26	Наталія	Коваль	3000-01-02	Чоловік
10	27	Іван	Петренко	2003-12-12	male
1	1	Олександр	Коваль	1999-12-12	Чоловік
\.


--
-- Data for Name: user_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.user_roles (user_role_id, user_id, role) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (user_id, username, password_hash, email, phone_number, registration_date, address, role, status, balance) FROM stdin;
21	ast5	$2y$10$6slXaAy5gWic/sSZnAY6duCuWfpFqO/dR4cE9ryJzGoHCTgAGdzUS	dmytro.melnyk@example.com	+380661234571	2024-06-11 20:29:47.408973	вул. Прорізна, 7, Київ	user	online	0.00
23	ast6	$2y$10$6PiaZSimEbZNMVeO5fBO8.z303qEYMWEgF4Vs8HHnfxif.5yjpJLu	vika.bog@gmail.com	+380918544569	2024-06-11 20:43:41.920238	вул. Хмельницького, 22, Київ	user	online	0.00
27	ast12	$2y$10$T4rWBtfp3ExnrxdriS1zBuHDL7gDN8ZAzOejLQMBvK9CSAybhYR6G	kruty105@mailto.plus	+380931234569	2024-06-29 19:18:09.03396	вул. Хрещатик, 22, Київ	user	online	0.00
1	ast1	$2y$10$ddSaRtl2aU9vEGwXOM9Iz.7iQAuHM1gh8yZz.MUuP.cJLzFRi1MWO	oleksandr.koval@example.com	+380501234567	2024-06-11 14:17:19.656293	вул. Хрещатик, 22, Київ	user	offline	700.00
22	admin	$2y$10$zQbEACF2N1H08cqWVQKWSuVYzp7P4Mg98MzzMtNiPZXNaZA9/5sLu	admin@astroconnect.com	+13942	2024-06-11 20:38:24.113136	Кузнечна вулиця, 1, Одеса	admin	online	0.00
4	ast3	$2y$10$frCqhhXNz3H1uAFVZLeqQuUGAPuz/rF6y5BPEgi8vaKPQayyyfuvq	ivan.petrenko@example.com	+380931234569	2024-06-11 17:54:18.206771	вул. Володимирська, 3, Київ	user	online	0.00
8	ast4	$2y$10$AfDaVd0FqeuRGDVmLUQge.h57OeyKI/Q7DIbBkDggJpwh7K0OPzcW	anna.smirnova@example.com	+380991234570	2024-06-11 19:42:38.27567	вул. Січових Стрільців, 1, Київ	user	online	0.00
3	ast2	$2y$10$ETU6TEEi5yKJxMka6j62peRPYS5fy0DnQdTssyXtnzstYatRyp3B.	natalia.shevchenko@example.com	+380671234568	2024-06-11 17:43:22.951415	вул. Лесі Українки, 14, Київ	user	online	600.00
25	ast10	$2y$10$kiLhqQXqyRt3BijZVQFVVuGZxBhIWmBlRo0wgYB7aKDUHiFxDaBS2	kruty102@mailto.plus	+380931234569	2024-06-29 15:28:36.30012	вул. Січових Стрільців, 1, Київ	user	online	0.00
26	ast11	$2y$10$UNGi1U55fszZcqSO0/35rezj/NbZA8F62zPf8T/mF1jIfAw9lCUF6	kruty100@mailto.plus	+380991234570	2024-06-29 15:33:51.91237	вул. Володимирська, 3, Київ	user	online	300.00
\.


--
-- Name: activity_log_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.activity_log_log_id_seq', 1, false);


--
-- Name: connections_connection_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.connections_connection_id_seq', 21, true);


--
-- Name: payments_payment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payments_payment_id_seq', 15, true);


--
-- Name: permissions_permission_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permissions_permission_id_seq', 1, false);


--
-- Name: tariffs_tariff_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tariffs_tariff_id_seq', 6, true);


--
-- Name: user_profiles_profile_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.user_profiles_profile_id_seq', 10, true);


--
-- Name: user_roles_user_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.user_roles_user_role_id_seq', 1, false);


--
-- Name: users_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_user_id_seq', 27, true);


--
-- Name: activity_log activity_log_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_log
    ADD CONSTRAINT activity_log_pkey PRIMARY KEY (log_id);


--
-- Name: connections connections_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.connections
    ADD CONSTRAINT connections_pkey PRIMARY KEY (connection_id);


--
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (payment_id);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (permission_id);


--
-- Name: tariffs tariffs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tariffs
    ADD CONSTRAINT tariffs_pkey PRIMARY KEY (tariff_id);


--
-- Name: user_profiles user_profiles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_profiles
    ADD CONSTRAINT user_profiles_pkey PRIMARY KEY (profile_id);


--
-- Name: user_roles user_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_pkey PRIMARY KEY (user_role_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: idx_activity_log_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_activity_log_user_id ON public.activity_log USING btree (user_id);


--
-- Name: idx_connections_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_connections_user_id ON public.connections USING btree (user_id);


--
-- Name: idx_payments_user_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_payments_user_id ON public.payments USING btree (user_id);


--
-- Name: idx_users_email; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_email ON public.users USING btree (email);


--
-- Name: idx_users_username; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_users_username ON public.users USING btree (username);


--
-- Name: activity_log activity_log_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_log
    ADD CONSTRAINT activity_log_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- Name: connections connections_tariff_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.connections
    ADD CONSTRAINT connections_tariff_id_fkey FOREIGN KEY (tariff_id) REFERENCES public.tariffs(tariff_id);


--
-- Name: connections connections_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.connections
    ADD CONSTRAINT connections_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- Name: payments payments_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- Name: user_profiles user_profiles_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_profiles
    ADD CONSTRAINT user_profiles_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- Name: user_roles user_roles_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

