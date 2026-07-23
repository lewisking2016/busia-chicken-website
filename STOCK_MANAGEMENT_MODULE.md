# STOCK MANAGEMENT MODULE

This is not just a simple inventory counter. In your system, this module is the **intelligent brain** that controls everything related to your physical inventory, from the moment raw materials arrive to the moment finished bags are ready for sale.

## 1. What is the "Stock Management Module"?
It is the central system that **manages, calculates, and values** all the physical goods in your warehouse. 

Unlike a basic system that only tells you **"You have 10 bags of maize,"** your Stock Management Module is **dynamic and formula-driven**. It treats your raw materials (in tons) and finished products (in bags) as interconnected data that continuously influence each other.

## 2. The 6 Core Functions of This Module
This module handles six specific jobs automatically:

| # | Function | What it does |
|---|---|---|
| 1 | Raw Material Intake | Records all incoming raw materials in **tons** and stores their **current buying price** per ton. |
| 2 | Recipe Configuration | Holds all your feed recipes (e.g., Chick Mash, Layers Mash). It knows exactly how many kg of each raw material are needed to make **1 finished bag** of any size (50kg, 70kg, 100kg, etc.). |
| 3 | Automatic Conversion (Tons to Bags) | When you request production, this module calculates exactly how many **full bags** you can produce from your current raw material stock. |
| 4 | Bottleneck & Leftover Logic | It identifies the **limiting ingredient** (the one that runs out first) and automatically suggests alternative bag sizes (e.g., 15kg) to use up leftover materials so nothing goes to waste. |
| 5 | Real-Time Costing (COGS) | It uses the raw material buying prices you entered to instantly calculate the **exact cost** of producing a single finished bag. |
| 6 | Price Fluctuation Alerts | It compares every new raw material price against older prices. If the cost of an ingredient rises or falls, it immediately calculates the impact on your bag costs and **alerts you** to adjust your selling prices. |

## 3. How This Module Interacts with Others
In your overall system, this Stock Management Module works closely with other modules but remains independent in its job:

- **Purchasing Module:** It receives the raw material data (tons and prices) from your purchase orders.
- **Production Module:** It sends the "Production Plan" (e.g., "Produce 595 bags of 70kg") to the production team.
- **Sales Module:** It provides the **real-time cost** of each bag so the Sales module knows the minimum price to charge. It also sends alerts (via the Price Fluctuation feature) to update sales prices.
- **Accounting Module:** It tracks the financial value of your inventory (total value of stock in dollars/kes).

## 4. What the User (Manager) Sees in This Module
When you open the "Stock Management" menu in your system, you will see:

- **Live Stock Dashboard:** A single screen showing all raw materials in tons, all finished bags in stock, and total inventory value.
- **Production Calculator:** A tool where you select a feed type and bag size, and it instantly shows you the maximum bags you can make right now.
- **Leftover Optimizer:** A screen that automatically proposes smaller bag sizes to exhaust all leftover ingredients.
- **Cost & Margin Viewer:** Shows the current cost per bag, your selling price, and your profit margin.
- **Alert Center:** A list of notifications like "Maize price increased—update Chick Mash price from $30 to $31.78."

## In Simple Terms
Stock Management is the module that answers your daily questions:
- "How many tons of maize do I have?" → Check Stock Management.
- "If I mix everything, how many 70kg bags can I make?" → Check Stock Management.
- "I have leftovers—how do I use them?" → Check Stock Management.
- "Did the price of soya go up, and how does that affect my bag price?" → Check Stock Management.

This module is the **heart** of your entire system. Everything else—sales, purchasing, and production—will depend on the accurate data provided by this Stock Management module. Without it, your system is just a sales register; with it, it becomes a fully automated feed production management system.
