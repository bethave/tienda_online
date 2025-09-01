select * from usuarios;
select * from usuario_cliente;
select * from cliente;
select * from pedido_cliente;
select * from productos;
select * from stock;

create table usuario_cliente(
usu_id integer,
cli_id integer,
uc_estado varchar,
primary key (usu_id, cli_id)
);

alter table cliente drop column usu_id;

create table pedido_cliente (
  ped_id integer primary key,
  ped_fecha date,
  ped_estado varchar,
  ped_auditoria varchar,
  cli_id integer,
  foreign key (cli_id) references cliente (cli_id)
);

create table pedido_cli_detalle (
  ped_id integer,
  prod_cod integer,
  pc_cantidad numeric,
  pc_montototal numeric,
  primary key (ped_id, prod_cod)
);

create table stock (
  prod_cod integer,
  suc_cod integer,
  st_estado varchar,
  st_cantidad_maxima numeric,
  st_cantidad_minima numeric,
  st_cantidad_total numeric,
  primary key (prod_cod, suc_cod)
);