CREATE TABLE user(

    username VARCHAR(20),
    email VARCHAR(30),
    phone  VARCHAR(20),
    address_ VARCHAR(50),
    password_ VARCHAR(256),
    role_ VARCHAR(10),
    PRIMARY KEY (username,email)

);


CREATE table distances(
    location_ VARCHAR(20),
    distances INT,
    primary key (location_)

);



CREATE TABLE company(
    username VARCHAR(20),
    password_ VARCHAR(256),
    businessID INT PRIMARY KEY,
    totalDriver INT,
    totalVehicle INT,
    rating INT DEFAULT 0
);

CREATE TABLE driver(
    username VARCHAR(20),
    password_ VARCHAR(256),
    lisenceNo VARCHAR(30),
    lisenceLev VARCHAR(30),
    phone VARCHAR(20),
    address_ VARCHAR(50),
    accident_history INT,
    companyID VARCHAR(10),
    rating INT DEFAULT 0,
    businessID INT,
    status_ VARCHAR(15) DEFAULT "Available",
    PRIMARY KEY(username, lisenceNo),
    FOREIGN KEY (businessID) REFERENCES company(businessID)
);

CREATE TABLE trip (
    tripID INT AUTO_INCREMENT,
    user VARCHAR(20),
    driver VARCHAR(20),
    companyID VARCHAR(10),
    location VARCHAR(50),
    destination VARCHAR(50),
    distance INT,
    price DECIMAL(10, 2),
    start_time DATETIME,  -- New field for starting time
    end_time DATETIME,  -- New field for starting time
    status VARCHAR(20) DEFAULT 'pending',
    businessID INT,
    PRIMARY KEY (tripID),
    FOREIGN KEY (user) REFERENCES user(username),
    FOREIGN KEY (driver) REFERENCES driver(username),
    FOREIGN KEY (businessID) REFERENCES company(businessID)
);

CREATE TABLE car (
    carID INT AUTO_INCREMENT,
    model VARCHAR(50),
    licensePlate VARCHAR(20),
    status VARCHAR(20) DEFAULT 'available',
    businessID INT,
    PRIMARY KEY (carID),
    FOREIGN KEY (businessID) REFERENCES company(businessID)
);

CREATE TABLE revenue (
    revenueID INT AUTO_INCREMENT PRIMARY KEY,
    businessID INT,
    driverUsername VARCHAR(20),
    totalCompanyRevenue DECIMAL(10, 2) DEFAULT 0.00,
    totalDriverEarnings DECIMAL(10, 2) DEFAULT 0.00,
    lastUpdated DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (businessID) REFERENCES company(businessID),
    FOREIGN KEY (driverUsername) REFERENCES driver(username)
);

CREATE TABLE driver_availability (
    availabilityID INT AUTO_INCREMENT PRIMARY KEY,
    driverUsername VARCHAR(20),
    booked_from DATETIME NOT NULL,
    booked_to DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (driverUsername) REFERENCES driver(username)
);

-- Creating Garage Table
CREATE TABLE garage (
    garageID INT AUTO_INCREMENT PRIMARY KEY,
    ownerUsername VARCHAR(20),  -- Could be a company or individual user
    location VARCHAR(50),
    capacity INT,  -- Total number of vehicles the garage can hold
    availableSpaces INT,  -- Number of currently available spaces
    price_per_day DECIMAL(10, 2),  -- Rental price per day for using the garage
    status VARCHAR(20) DEFAULT 'available',  -- Can be 'available', 'full', or 'unavailable'
    FOREIGN KEY (ownerUsername) REFERENCES user(username)
);

-- Creating Garage Booking Table
CREATE TABLE garage_booking (
    bookingID INT AUTO_INCREMENT PRIMARY KEY,
    garageID INT,
    renterUsername VARCHAR(20),  -- User or company renting the garage space
    start_date DATETIME,
    end_date DATETIME,
    total_price DECIMAL(10, 2),  -- Total price based on the number of days booked
    status VARCHAR(20) DEFAULT 'pending',  -- Can be 'pending', 'confirmed', or 'cancelled'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (garageID) REFERENCES garage(garageID),
    FOREIGN KEY (renterUsername) REFERENCES user(username)
);

-- Creating Garage Availability Table
CREATE TABLE garage_availability (
    availabilityID INT AUTO_INCREMENT PRIMARY KEY,
    garageID INT,
    available_from DATETIME,
    available_to DATETIME,
    FOREIGN KEY (garageID) REFERENCES garage(garageID)
);

-- Creating Garage Revenue Table
CREATE TABLE garage_revenue (
    revenueID INT AUTO_INCREMENT PRIMARY KEY,
    garageID INT,
    ownerUsername VARCHAR(20),  -- Owner of the garage
    totalRevenue DECIMAL(10, 2) DEFAULT 0.00,
    lastUpdated DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (garageID) REFERENCES garage(garageID),
    FOREIGN KEY (ownerUsername) REFERENCES user(username)
);


CREATE TABLE car_exchange_request (
    requestID INT AUTO_INCREMENT PRIMARY KEY,
    requestingCompanyID INT,
    requestedCompanyID INT,
    carID INT,
    status VARCHAR(20) DEFAULT 'pending',
    requestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requestingCompanyID) REFERENCES company(businessID),
    FOREIGN KEY (requestedCompanyID) REFERENCES company(businessID),
    FOREIGN KEY (carID) REFERENCES car(carID)
);

CREATE TABLE driver_exchange_request (
    requestID INT AUTO_INCREMENT PRIMARY KEY,
    requestingCompanyID INT,
    requestedCompanyID INT,
    driverUsername VARCHAR(20),
    status VARCHAR(20) DEFAULT 'pending',
    requestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requestingCompanyID) REFERENCES company(businessID),
    FOREIGN KEY (requestedCompanyID) REFERENCES company(businessID),
    FOREIGN KEY (driverUsername) REFERENCES driver(username)
);
-- Insert example data for testing

-- Example user inserting garages
INSERT INTO garage (ownerUsername, location, capacity, availableSpaces, price_per_day, status)
VALUES ('company1', '123 Street City A', 10, 8, 20.50, 'available'),
       ('user2', '456 Avenue City B', 5, 3, 15.00, 'available');

-- Example of adding a garage booking
INSERT INTO garage_booking (garageID, renterUsername, start_date, end_date, total_price, status)
VALUES (1, 'user3', '2024-10-05 08:00:00', '2024-10-10 08:00:00', 102.50, 'pending');

-- Example of adding garage availability
INSERT INTO garage_availability (garageID, available_from, available_to)
VALUES (1, '2024-10-01 08:00:00', '2024-10-31 08:00:00');

-- Example of garage revenue entry
INSERT INTO garage_revenue (garageID, ownerUsername, totalRevenue, lastUpdated)
VALUES (1, 'company1', 0.00, CURRENT_TIMESTAMP);





drop table driver_availability;

SELECT * from driver_availability;

SELECT * FROM driver_availability WHERE driverUsername = "new" AND 
                                   (available_from > "2024-10-04 13:27:00" AND available_to < "2024-10-04 13:30:58");


SELECT * FROM driver_availability WHERE driverUsername = "new" AND 
            available_from < 2024-10-04 13:27:00 AND available_to > 2024-10-04 13:30:58

DELETE FROM trip;
DELETE FROM driver_availability;


UPDATE driver SET status_ = 'available' WHERE username = "zarif"

insert into company(username,password_,businessID,totalDriver,totalVehicle) values(?,?,?,?,?)
insert into driver(username,password_,lisenceNo,lisenceLev,phone,address_,accident_history,companyID) values(?,?,?,?,?,?,?,?);
insert into user(username, phone, email, address_, password_, role_) values("Ekanto","011","ekanto@ekanto","dhaka","1234", "user");

SELECT * from company driver where username="ekanto";

SELECT password_ FROM user WHERE username = "ekanto2";

drop table user;
drop table driver;
drop table company;
drop table trip;
drop table car;
drop table distances;
drop table revenue;
drop table driver_availability;

delete from trip;

SELECT * FROM company;

INSERT INTO distances (location_, distances) VALUES
('Location 1', 1),
('Location 2', 3),
('Location 3', 6),
('Location 4', 10),
('Location 5', 15),
('Location 6', 22),
('Location 7', 28),
('Location 8', 35),
('Location 9', 43),
('Location 10', 50),
('Location 11', 58),
('Location 12', 67),
('Location 13', 75),
('Location 14', 84),
('Location 15', 92),
('Location 16', 101),
('Location 17', 110),
('Location 18', 120),
('Location 19', 130),
('Location 20', 140);

-- Inserting a few cars under businessID 1
INSERT INTO car (model, licensePlate, status, businessID) 
VALUES ('Toyota Corolla', 'ABC-1234', 'available', 1);

INSERT INTO car (model, licensePlate, status, businessID) 
VALUES ('Honda Civic', 'DEF-5678', 'available', 1);

INSERT INTO car (model, licensePlate, status, businessID) 
VALUES ('Ford Focus', 'GHI-9012', 'available', 1);



ALTER TABLE car ADD businessID INT;
ALTER TABLE driver ADD businessID INT;
ALTER TABLE trip ADD businessID INT;
