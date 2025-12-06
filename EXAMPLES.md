# Usage Examples & Scenarios

## 📚 Real-World Use Cases

### Scenario 1: Retail Store Opening Day

**Goal**: Set up inventory for a new store

**Steps**:
1. Open `index.html`
2. Go to "Inventory Management" tab
3. Click "Add New Item" for each product:
   ```
   Product 1: Wireless Headphones
   - SKU: WH-BL001
   - Barcode: 1234567890123
   - Category: Electronics
   - Stock: 15
   - Price: $129.99
   - Min Stock: 5
   ```

4. Add more items similarly:
   ```
   Product 2: USB-C Cable
   - SKU: USB-001
   - Barcode: 1234567890124
   - Stock: 50
   - Price: $19.99
   - Min Stock: 20
   
   Product 3: T-Shirt - Blue
   - SKU: TS-BL001
   - Barcode: 1234567890125
   - Stock: 100
   - Price: $24.99
   - Min Stock: 30
   ```

5. Check Dashboard to see:
   - Total Inventory: 165 units
   - All items shown in charts
   - Categories distributed

---

### Scenario 2: Daily Operations - Barcode Scanning

**Goal**: Quickly lookup product info during sales

**Steps**:
1. Customer asks: "How much is the wireless headphones?"
2. Click "Barcode Scanner" tab
3. Scan product barcode (or type: 1234567890123)
4. Press Enter
5. Instantly see:
   ```
   Product Name: Wireless Headphones
   Stock: 15 units
   Unit Price: $129.99
   Status: In Stock ✅
   ```
6. Ready to sell!

**Alternative**: Click "Inventory" → Search box → Type "headphones" → Instant results

---

### Scenario 3: Stock Update After Sales

**Goal**: Update inventory when products sell

**Steps**:
1. Sold 3 Wireless Headphones
2. Go to "Inventory Management"
3. Search: "Wireless" (auto-filtered)
4. Click "Edit" button
5. Change Stock from 15 → 12
6. Click "Save Item"
7. Check Dashboard:
   ```
   Total Inventory: 162 ✓ (decreased)
   Status still: In Stock ✓
   Chart updated ✓
   ```

---

### Scenario 4: Low Stock Alert

**Goal**: Notice when items need reorder

**Steps**:
1. Go to "Inventory Management"
2. Stock Filter → "Low Stock"
3. See all items below minimum:
   ```
   USB-C Cable: 3 items (Min: 20) ⚠️
   Yoga Mat: 5 items (Min: 8) ⚠️
   ```
4. Time to reorder!

---

### Scenario 5: Edit Product Information

**Goal**: Correct product details

**Steps**:
1. Noticed wrong price for T-Shirt
2. Go to "Inventory Management"
3. Search: "T-Shirt"
4. Click "Edit"
5. Change Price: 24.99 → 29.99
6. Click "Save Item"
7. Change instantly reflected
8. Dashboard metrics update

---

### Scenario 6: Remove Discontinued Item

**Goal**: Delete product no longer sold

**Steps**:
1. Yoga Mat is discontinued
2. Go to "Inventory Management"
3. Search: "Yoga"
4. Click "Delete" button
5. Confirm deletion in modal
6. Item removed
7. Dashboard updates:
   ```
   Total Inventory: 157 ✓ (decreased)
   "Out of Stock" count may change ✓
   ```

---

### Scenario 7: End of Day Report

**Goal**: Review sales performance

**Steps**:
1. End of day 5 PM
2. Go to "Sales Reports" tab
3. Select period: "Daily"
4. View charts:
   - Daily Sales line chart (sales throughout day)
   - Sales by Category (what sold most)
5. Scroll down for detailed transaction table
6. See total daily revenue

---

### Scenario 8: Weekly Inventory Audit

**Goal**: Verify physical stock matches system

**Steps**:
1. Physical audit completed
2. Found discrepancy: Headphones actually 14, system shows 15
3. Go to "Inventory Management"
4. Search: "Headphones"
5. Click "Edit"
6. Change Stock: 15 → 14
7. Click "Save Item"
8. System now accurate ✓

---

### Scenario 9: Search by Multiple Filters

**Goal**: Find specific products

**Steps**:

**Example A - Find all electronics:**
```
Category Filter: Electronics → See all electronics items
```

**Example B - Find low stock items in one category:**
```
1. Category Filter: Electronics
2. Stock Filter: Low Stock → See electronics with stock < minimum
```

**Example C - Search by name:**
```
Search box: Type "USB" → Instantly shows all USB products
```

**Example D - Search by barcode:**
```
Barcode Scanner tab: Scan/type barcode → Instant product lookup
```

---

### Scenario 10: Monthly Analytics Review

**Goal**: Analyze business trends

**Steps**:
1. Go to "Sales Reports"
2. Period: "Monthly"
3. View Monthly Sales Trend chart
4. Notice sales pattern:
   ```
   Jan: $35k
   Feb: $41k ↑
   Mar: $38k ↓
   Apr: $45k ↑
   May: $52k ↑ (best month!)
   Jun: $54k ↑
   ```
5. See "Sales Distribution" pie chart
6. Note: Electronics = 40% of sales (top category)
7. Use insights for inventory ordering

---

## 🎯 Feature Demonstrations

### Feature 1: Add Item Demo

```javascript
// What happens when you "Add New Item"

// Modal opens with empty form:
SKU: [empty]
Product Name: [empty]
Barcode: [empty]
Category: [Select Category ▼]
Stock: [0]
Unit Price: [0.00]
Min Stock: [10]

// You fill in:
SKU: DEMO001
Product Name: Demo Product
Barcode: 9876543210987
Category: Electronics
Stock: 25
Unit Price: 49.99
Min Stock: 8

// System validates:
✓ All fields filled
✓ Barcode unique (not already in system)
✓ Stock is number >= 0
✓ Price is positive decimal

// You click "Save Item"
✓ Item added to inventory
✓ Saved to localStorage
✓ Table re-rendered with new item
✓ Dashboard metrics updated
✓ Charts refreshed
✓ Success notification shown
```

---

### Feature 2: Barcode Lookup Demo

```javascript
// User on "Barcode Scanner" tab
// Input field auto-focused (cursor blinking)

// User scans barcode: 1234567890123
// OR types manually: 1234567890123
// Presses Enter

// System searches inventory:
✓ Found: Wireless Headphones

// Display shows:
┌─────────────────────────────────┐
│ Product Name: Wireless Headphones│
│ SKU: SKU001                      │
│ Barcode: 1234567890123          │
│ Category: Electronics            │
│ Stock Quantity: 45               │
│ Unit Price: $129.99              │
│ Status: In Stock ✅              │
│ Min. Stock Level: 10             │
└─────────────────────────────────┘

// Alert shows: ✓ Item found!
// Input clears, ready for next scan
```

---

### Feature 3: Edit Demo

```javascript
// User on "Inventory Management"
// Finds: USB-C Cable in table
// Clicks "Edit" button

// Modal opens with current values:
SKU: SKU002
Product Name: USB-C Cable
Barcode: 1234567890124
Category: Electronics
Stock: 3 ← [CURRENT - LOW!]
Unit Price: 19.99
Min Stock: 20

// User updates stock: 3 → 50
// User clicks "Save Item"

// System validates:
✓ Barcode still unique
✓ All required fields filled
✓ Stock is valid number

// Updates apply:
✓ Item updated in array
✓ Saved to localStorage
✓ Table re-rendered (shows 50)
✓ Dashboard updates:
  - Total Inventory: +47
  - Low Stock Items: -1
✓ Charts refreshed
✓ Status badge changes from ⚠️ to ✅
```

---

### Feature 4: Delete Demo

```javascript
// User on "Inventory Management"
// Finds: Office Chair (0 stock)
// Clicks "Delete" button

// Confirmation modal appears:
╔════════════════════════════════╗
║ Delete Item                    ║
║                                ║
║ Are you sure you want to      ║
║ delete this item? This        ║
║ action cannot be undone.      ║
║                                ║
║ [Cancel]  [Delete]            ║
╚════════════════════════════════╝

// User clicks "Delete"

// Item removed:
✓ Removed from inventory array
✓ Saved to localStorage
✓ Table re-rendered (no longer visible)
✓ Dashboard metrics update:
  - Total Inventory: -0 (was 0 stock)
  - Out of Stock: -1
✓ Charts refreshed
✓ Success notification: "Office Chair deleted!"
```

---

### Feature 5: Filter Demo

```javascript
// Scenario: User wants to see low stock electronics

// Step 1: Category filter
┌─ Select Category ────────────┐
│ ▼ All Categories            │
│   Electronics              │ ← Click
│   Clothing                 │
│   Home & Garden            │
│   Sports                   │
└──────────────────────────────┘

// Step 2: Stock status filter
┌─ Select Status ──────────────┐
│ ▼ All Items                 │
│   In Stock                 │
│   Low Stock                │ ← Click
│   Out of Stock             │
└──────────────────────────────┘

// Results: Table shows only
┌────────────────────────────────────┐
│ USB-C Cable   | 3 items | ⚠️       │
│ Mouse Pad*    | (45)    | ✅       │
│ Desk Lamp*    | (18)    | ✅       │
│ Yoga Mat      | 5 items | ⚠️       │
└────────────────────────────────────┘
* May vary by min stock levels

// All other electronics shown normally
// All non-electronics hidden
// All in-stock items hidden
```

---

## 🚦 Common Workflows

### Workflow 1: Morning Opening Checklist
```
1. Open app (data loads from localStorage)
2. Go to Dashboard → Check metrics
3. Inventory Management → Filter "Low Stock"
4. Note items needing reorder
5. Check "Out of Stock" items
6. Ready to open store!
```

### Workflow 2: During Day - Customer Service
```
1. Customer asks about product
2. Quick Search → Type product name
   OR Barcode Scanner → Scan product
3. Instant information displayed
4. Ready to help customer!
```

### Workflow 3: After Sale
```
1. Sale completed
2. Update quantities:
   - Search product
   - Click Edit
   - Update stock
   - Save
3. Dashboard automatically reflects change
```

### Workflow 4: End of Day
```
1. Go to Reports tab
2. Select Daily
3. Review charts and transactions
4. Note if any items went low
5. Plan for tomorrow's reorders
```

---

## 📊 Sample Data

### Electronics Category
| SKU | Product | Barcode | Stock | Price | Min Stock |
|-----|---------|---------|-------|-------|-----------|
| SKU001 | Wireless Headphones | 1234567890123 | 45 | $129.99 | 10 |
| SKU002 | USB-C Cable | 1234567890124 | 3 | $19.99 | 20 |
| SKU007 | Mouse Pad | 1234567890129 | 156 | $12.99 | 50 |

### Fashion Category
| SKU | Product | Barcode | Stock | Price | Min Stock |
|-----|---------|---------|-------|-------|-----------|
| SKU003 | T-Shirt Blue | 1234567890125 | 120 | $24.99 | 30 |
| SKU004 | Running Shoes | 1234567890126 | 32 | $89.99 | 15 |

### Home & Sports
| SKU | Product | Barcode | Stock | Price | Min Stock |
|-----|---------|---------|-------|-------|-----------|
| SKU005 | Office Chair | 1234567890127 | 0 | $199.99 | 5 |
| SKU006 | Desk Lamp | 1234567890128 | 18 | $45.99 | 10 |
| SKU008 | Yoga Mat | 1234567890130 | 5 | $34.99 | 8 |

---

## 🎓 Learning Path

### Beginner
1. Open app and explore Dashboard
2. Go to Inventory, view existing items
3. Try Search and Filters
4. Try Barcode Scanner

### Intermediate
1. Add a new product
2. Edit existing product
3. Delete a product
4. View Reports

### Advanced
1. Export inventory data (copy from DevTools)
2. Back up data (save JSON)
3. Restore data (import JSON)
4. Monitor metrics for business insights

---

## ✨ Pro Tips

💡 **Tip 1**: Use barcodes efficiently
- Scan product barcode, info appears instantly
- Search by barcode for fastest lookup

💡 **Tip 2**: Set realistic minimum stock
- Prevents overselling
- Triggers low stock alerts
- Helps plan reorders

💡 **Tip 3**: Monitor dashboard daily
- Quick view of inventory health
- Spot trends in charts
- Plan purchasing based on sales

💡 **Tip 4**: Backup data regularly
- Copy inventoryData from DevTools
- Save to external file
- Can restore if browser data cleared

💡 **Tip 5**: Use search/filters
- Much faster than scrolling
- Filter by multiple criteria
- Sort alphabetically

💡 **Tip 6**: Update quantities daily
- Keeps system accurate
- Dashboard metrics stay reliable
- Charts show real trends

---

**Master these workflows and you'll use the system efficiently!**
