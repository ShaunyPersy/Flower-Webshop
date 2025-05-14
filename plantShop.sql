create database PlantShop;
use plantShop;

create table User
(
    UserID bigint(8) unsigned auto_increment,
    UserName varchar(32),
    UserPassword varchar(255),
    UserType varchar(32),
    primary key (UserID)
);

create table Customer
(
    CustomerID bigint(8) unsigned auto_increment,
    UserID bigint(8) unsigned,
    CustomerFirstName varchar(32),
    CustomerLastName varchar(32),
    CustomerAddress varchar(32),
    CustomerEmail varchar(32),
    primary key (CustomerID),
    foreign key (UserID) references User(UserID)
);

create table `Order`
(
    OrderID bigint(8) unsigned auto_increment,
    CustomerID bigint(8) unsigned,
    OrderDate date,
    OrderStatus varchar(10),
    OrderTotalAmount int,
    OrderPaymentMethod varchar(32),
    primary key (OrderID),
    foreign key (CustomerID) references Customer(CustomerID)
);

create table Payment
(
    PaymentID bigint(8) unsigned auto_increment,
    OrderID bigint(8) unsigned,
    PaymentDate date,
    PaymentStatus varchar(10),
    primary key (PaymentID),
    foreign key (OrderID) references `Order`(OrderID)
);

create table Plant
(
    PlantID bigint(8) unsigned auto_increment,
    PlantName varchar(32),
    PlantDescription varchar(255),
    PlantCare varchar(255),
    PlantPrice int,
    PlantQuantityInStock int,
    PlantPicture varchar(255),
    primary key (PlantID)
);

create table OrderLine
(
    OrderLineID bigint(8) unsigned auto_increment,
    OrderID bigint(8) unsigned,
    PlantID bigint(8) unsigned,
    OrderLineQuantity int,
    primary key (OrderLineID),
    foreign key (OrderID) references `Order`(OrderID),
    foreign key (PlantID) references Plant(PlantID)
);

create table Review
(
    ReviewID bigint(8) unsigned auto_increment,
    PlantID bigint(8) unsigned,
    CustomerID bigint(8) unsigned,
    ReviewText varchar(255),
    primary key (ReviewID),
    foreign key (PlantID) references Plant(PlantID),
    foreign key (CustomerID) references Customer(CustomerID)
);

create table Category
(
    CategoryID bigint(8) unsigned auto_increment,
    CategoryName varchar(32),
    CategoryDescription varchar(255),
    primary key (CategoryID)
);

create table PlantCategory
(
    PlantCategoryID bigint(8) unsigned auto_increment,
    PlantID bigint(8) unsigned,
    CategoryID bigint(8) unsigned,
    primary key (PlantCategoryID),
    foreign key (PlantID) references Plant(PlantID),
    foreign key (CategoryID) references Category(CategoryID)
);