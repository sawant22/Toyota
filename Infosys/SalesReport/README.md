# Module Infosys SalesReport

	- [Installation](#markdown-header-installation)
	- [Configuration](#markdown-header-configuration)
	- [Main Functionalities](#markdown-header-main-functionalities)

## Installation

	- Enable the module by running `php bin/magento module:enable Infosys_SalesReport`
	- Apply database updates by running `php bin/magento setup:upgrade` command
	- Generate static content for Admin html by running `php bin/magento setup:static-content:deploy -f --area adminhtml`
	- Flush the cache by running `php bin/magento cache:flush`

## Configuration

	- Store Configuration: Setup following store configuration under the Stores->Toyota->Sales Report Configuration
	- Logs: It's used to enable/disable logs
	- Maximum Records Count to Calculate Report: It's used when we calculate dealer statistics sales report, based on the count we are calculating sales report

## Main Functionalities
	1. In this module, we are creating a report to calculate the rank of dealer as per the selected filter options and displaying the result in the backend grid format along with export feature.

	2. We have created the table, toyota_dealer_sales_statistics where we're keeping all data of sales on the basis of date and store Id.

	3. In order to calculate and save the sales statistics, we used the Store Procedure that's calculating all the sales statistics and save the result into toyota_dealer_sales_statistics table.

	4. We have a cron job that will run daily to pull the list of all store_ids and insert it into the toyota_dealer_sales_statistics_queue table.

	5. We have a cron job that call the Store Procedure at daily basis to calculate yesterday's sales record. 

	6. We are also upgrading the data in the toyota_dealer_sales_statistics table when any order is updated from the backend.

	7. We have a CLI command to recalculate sales statistics for a given date range.
		CLI Command Ex: php bin/magento infosyssales_statistics:recalculate --store_id=1 --start_date=2022-04-04 --end_date=2022-04-06
		
	8. Backend Admin Grid - Backend user can generate the ranking report from the backend Report->Sales->Dealer Ranking Report Section by their roles. Roles include Corporate and Dealer
			
			- A corporate role would allow for choosing of any Brand, Region and Website(dealership) to run the report on.
			
			- If the role is of a dealer, then they would only be able to choose a website(dealership) that they are assigned to.
			
	9. Admin user will need to select the following inputs and based on the this the rank would be calculated
		
			- Brands
			- Region
			- Dealer
			- Date Range (From date to To date)
			
	10. The report would be shown in the grid form with the coloumn Dealer Name, Your performance, Other Performance (Low, Medium, High) and Rank.

	11. Admin user can export the report in the CSV/Excel XML format.


