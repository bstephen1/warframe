
create database if not exists warframe;
use warframe;

create table if not exists parts(
  name varchar(30) not null unique,
  primary key (name),
  ducats decimal(2) not null,
  platinum enum('low','med','high') not null
);

create table if not exists endless(
  ename varchar(30) not null,
  foreign key (ename) references parts(name) on delete cascade,
  tier enum('I', 'II', 'III', 'IV', 'derelict') not null,
  type enum('survival', 'defense', 'interception') not null,
  rotation enum('A', 'B', 'C') not null,
  primary key (ename, tier, type, rotation),
  chance decimal(3,1) not null,
  rarity enum('C', 'U', 'R', 'L') not null
);

create table if not exists not_endless(
  nename varchar(30) not null,
  foreign key (nename) references parts(name) on delete cascade,
  tier enum('I', 'II', 'III', 'IV') not null,
  type enum('capture', 'exterminate', 'sabotage', 'mobile defense') not null,
  primary key (nename, tier, type),
  chance decimal(3,1) not null,
  rarity enum('C', 'U', 'R', 'L') not null
);
  
