# Quick Reference Guide

## Navigation

| Tab | Purpose |
|-----|---------|
| 📊 Dashboard | View key metrics and sales charts |
| 📦 Inventory | Manage products (Add/Edit/Delete) |
| 📱 Barcode Scanner | Lookup products by barcode |
| 📈 Sales Reports | View detailed analytics by period |

## Inventory CRUD Operations

### ➕ CREATE (Add New Item)
```
1. Click "Add New Item" button
2. Fill form:
   - SKU: Unique identifier (e.g., SKU001)
   - Product Name: Full product name
   - Barcode: Unique barcode (e.g., 1234567890123)
   - Category: Electronics, Clothing, Home, or Sports
   - Stock: Current quantity
   - Unit Price: Price per item
   - Min Stock: Alert threshold
3. Click "Save Item"
```

### 📖 READ (View Inventory)
```
1. Go to "Inventory Management" tab
2. View table with all products
3. Status badges show:
   ✅ In Stock = Above min level
   ⚠️ Low Stock = At or below min level
   ❌ Out of Stock = Zero quantity
```

### ✏️ UPDATE (Edit Item)
```
1. Go to "Inventory Management" tab
2. Find product in table
3. Click "Edit" button (pencil icon)
4. Modify any field
5. Click "Save Item"
6. Changes save immediately
```

### 🗑️ DELETE (Remove Item)
```
1. Go to "Inventory Management" tab
2. Find product in table
3. Click "Delete" button (trash icon)
4. Confirm deletion in modal
5. Item removed permanently
```

## Search & Filtering

| Filter | Usage |
|--------|-------|
| Search Box | Type product name, SKU, or barcode |
| Category Filter | Select product category |
| Stock Filter | Show In Stock / Low Stock / Out of Stock |
| Sort Button | Sort alphabetically by name |

## Barcode Lookup

```
1. Go to "Barcode Scanner" tab
2. Large input field is auto-focused
3. Scan barcode OR type manually
4. Press ENTER
5. Product details display below:
   - All information populated
   - Status clearly shown
   - ✅ Item found or ❌ Not found
6. Input clears, ready for next scan
```

## Dashboard Metrics

| Metric | Updates When |
|--------|--------------|
| Total Sales | Sales recorded (demo value) |
| Total Inventory | Item added/edited/deleted |
| Low Stock Items | Item stock changes |
| Out of Stock | Item stock reaches 0 |

## Charts (Auto-Update)

- **Daily Sales**: Line chart of sales trend
- **Sales by Category**: Doughnut chart of inventory by category
- **Top Products**: Bar chart of products with most stock
- **Monthly Trend**: Line chart of sales progression

Charts update instantly when you:
- Add new item
- Update stock quantity
- Delete item
- Change category

## Data Storage

**Where**: Browser LocalStorage  
**Key**: `inventoryData`  
**Format**: JSON array of product objects  
**Persistence**: Survives browser close/restart  
**Capacity**: ~5-10MB (hundreds of items)

### View Data (DevTools)
```
F12 → Application → LocalStorage → Your Domain
```

### Reset Data
```
F12 → Application → LocalStorage → Right-click domain → Clear
OR
Settings → Clear browsing data → LocalStorage
```

## Status Meanings

| Status | Condition | Color |
|--------|-----------|-------|
| In Stock | Stock > Min Level | 🟢 Green |
| Low Stock | 0 < Stock ≤ Min Level | 🟡 Yellow |
| Out of Stock | Stock = 0 | 🔴 Red |

## Form Validation Rules

| Field | Rules |
|-------|-------|
| SKU | Required, any format |
| Product Name | Required, any length |
| Barcode | Required, must be unique |
| Category | Must select one |
| Stock | Required, 0 or positive |
| Unit Price | Required, positive decimal |
| Min Stock | Required, 0 or positive |

## Keyboard Shortcuts

| Key | Action |
|-----|--------|
| Enter | Submit form or scan barcode |
| Tab | Move to next field |
| Esc* | Close modal (* manual - click X) |

## Common Tasks

### Add 10 products quickly
1. Prepare SKU, names, barcodes in spreadsheet
2. Click "Add New Item" for each
3. Fill fields (copy/paste as needed)
4. All save to localStorage automatically

### Find low stock items
1. Go to Inventory
2. Stock Filter → "Low Stock"
3. See all items below minimum level

### Look up specific product
1. Inventory: Search by name/SKU
2. Scanner: Search by barcode (fastest)

### Check what's selling
1. Go to Dashboard
2. View "Sales by Category" chart
3. See "Top Products" chart
4. Review "Sales Reports" for details

### Track inventory value
1. Dashboard shows total items
2. Multiply by average price mentally
3. Or use "Sales Reports" analytics

## Tips for Best Results

✅ **Use unique barcodes** - Each product needs different barcode  
✅ **Set realistic min stock** - Prevents automatic stockouts  
✅ **Search by barcode** - Fastest product lookup method  
✅ **Update stock regularly** - Keep quantities accurate  
✅ **Backup data** - Copy LocalStorage value periodically  
✅ **Use categories** - Makes filtering and analytics better  
✅ **Descriptive names** - Makes searching easier  

## Troubleshooting

### Changes not saving?
- Check browser allows LocalStorage (not in private mode)
- Clear cache and reload
- Check browser console for errors (F12)

### Barcode not found?
- Verify exact barcode in inventory
- Check for spaces or typos
- Try searching by product name instead

### Charts not updating?
- Page auto-updates after changes
- If not, manually refresh (F5)
- Check browser console for errors

### Lost data?
- Check LocalStorage if cleared accidentally
- Only recoverable if you have backup
- Pro tip: Backup regularly via DevTools

---

**Need help?** Check the README.md for detailed documentation or inspect browser console (F12) for error messages.
