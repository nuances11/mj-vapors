-- Create Product table
CREATE TABLE Product (
                         ProductID INT AUTO_INCREMENT PRIMARY KEY,
                         ProductName VARCHAR(255),
                         Description TEXT
);

-- Create Attribute table
CREATE TABLE Attribute (
                           AttributeID INT AUTO_INCREMENT PRIMARY KEY,
                           AttributeName VARCHAR(255)
);

-- Create AttributeOption table
CREATE TABLE AttributeOption (
                                 OptionID INT AUTO_INCREMENT PRIMARY KEY,
                                 AttributeID INT,
                                 OptionName VARCHAR(255),
                                 FOREIGN KEY (AttributeID) REFERENCES Attribute(AttributeID)
);

-- Create ProductAttribute table
CREATE TABLE ProductAttribute (
                                  ProductAttributeID INT AUTO_INCREMENT PRIMARY KEY,
                                  ProductID INT,
                                  AttributeID INT,
                                  OptionID INT,
                                  Value VARCHAR(255),
                                  FOREIGN KEY (ProductID) REFERENCES Product(ProductID),
                                  FOREIGN KEY (AttributeID) REFERENCES Attribute(AttributeID),
                                  FOREIGN KEY (OptionID) REFERENCES AttributeOption(OptionID)
);

-- Create SKU table with Price
CREATE TABLE SKU (
                     SkuID INT AUTO_INCREMENT PRIMARY KEY,
                     ProductID INT,
                     AttributeOptionID INT,
                     Price DECIMAL(10, 2),
                     FOREIGN KEY (ProductID) REFERENCES Product(ProductID),
                     FOREIGN KEY (AttributeOptionID) REFERENCES AttributeOption(OptionID)
);

-- Create Branch table
CREATE TABLE Branch (
                        BranchID INT AUTO_INCREMENT PRIMARY KEY,
                        BranchName VARCHAR(255)
);

-- Create Inventory table
CREATE TABLE Inventory (
                           InventoryID INT AUTO_INCREMENT PRIMARY KEY,
                           BranchID INT,
                           SkuID INT,
                           StockQuantity INT,
                           FOREIGN KEY (BranchID) REFERENCES Branch(BranchID),
                           FOREIGN KEY (SkuID) REFERENCES SKU(SkuID)
);

-- Create Transaction table
CREATE TABLE Transaction (
                             TransactionID INT AUTO_INCREMENT PRIMARY KEY,
                             BranchID INT,
                             SkuID INT,
                             TransactionType VARCHAR(50),
                             Quantity INT,
                             TransactionDate DATE,
    -- Add other fields as needed for your specific use case
                             FOREIGN KEY (BranchID) REFERENCES Branch(BranchID),
                             FOREIGN KEY (SkuID) REFERENCES SKU(SkuID)
);

-- Create Employee table
CREATE TABLE Employee (
                          EmployeeID INT AUTO_INCREMENT PRIMARY KEY,
                          FirstName VARCHAR(255),
                          LastName VARCHAR(255)
    -- Add other fields as needed
);

-- Create TimeEntry table with time-related columns in seconds
CREATE TABLE TimeEntry (
                           TimeEntryID INT AUTO_INCREMENT PRIMARY KEY,
                           EmployeeID INT,
                           BranchID INT,
                           ClockIn DATETIME,
                           ClockOut DATETIME,
                           BreakTimeInSeconds INT,
                           TotalHoursWorkedInSeconds INT,
                           TotalOvertimeInSeconds INT,
                           FOREIGN KEY (EmployeeID) REFERENCES Employee(EmployeeID),
                           FOREIGN KEY (BranchID) REFERENCES Branch(BranchID)
);

-- Create SalesReport table with EmployeeID
CREATE TABLE SalesReport (
                             ReportID INT AUTO_INCREMENT PRIMARY KEY,
                             EmployeeID INT,
                             BranchID INT,
                             TotalSales DECIMAL(10, 2),
                             TotalSalesDate DATE,
                             FOREIGN KEY (EmployeeID) REFERENCES Employee(EmployeeID),
                             FOREIGN KEY (BranchID) REFERENCES Branch(BranchID)
);


SELECT BranchID, EmployeeID, SUM(TotalSales) AS TotalSales
FROM SalesReport
WHERE TotalSalesDate = '2024-02-01' -- Replace with the desired date
GROUP BY BranchID, EmployeeID;

-- Select a product and associated attribute options
SELECT
    Product.ProductID,
    Product.ProductName,
    Product.Description,
    Attribute.AttributeName,
    AttributeOption.OptionName
FROM
    Product
        JOIN
    ProductAttribute ON Product.ProductID = ProductAttribute.ProductID
        JOIN
    Attribute ON ProductAttribute.AttributeID = Attribute.AttributeID
        JOIN
    AttributeOption ON ProductAttribute.OptionID = AttributeOption.OptionID
WHERE
        Product.ProductID = 1;  -- Replace with the desired ProductID
