# Inventory & Sales Management System

A modern, responsive web application for managing inventory, processing sales, and tracking barcode lookups. Built with vanilla HTML, CSS, and JavaScript with persistent localStorage storage.

## 🎯 Features

### 1. **Inventory Management (CRUD Operations)**

#### Create
- Click **"Add New Item"** button to open modal
- Fill in product details:
  - SKU (Stock Keeping Unit)
  - Product Name
  - Barcode (unique identifier)
  - Category (Electronics, Clothing, Home & Garden, Sports)
  - Stock Quantity
  - Unit Price
  - Minimum Stock Level (for low stock alerts)
- Form validation prevents duplicate barcodes
- Auto-saves to localStorage

#### Read
- View all items in a clean, searchable inventory table
- See product details: SKU, Name, Barcode, Category, Stock, Price, Status
- Color-coded badges: ✅ In Stock, ⚠️ Low Stock, ❌ Out of Stock
- Real-time dashboard metrics update

#### Update
- Click **"Edit"** button on any item
- Modify any field (all fields except duplicate barcode validation)
- Changes persist in localStorage
- Dashboard and charts update instantly

#### Delete
- Click **"Delete"** button on any item
- Confirmation modal prevents accidental deletions
- Item removed from inventory and localStorage
- Dashboard metrics update automatically

### 2. **Advanced Search & Filtering**

- **Search**: Find items by name, SKU, or barcode
- **Category Filter**: Filter by product category
- **Stock Status Filter**:
  - In Stock (above minimum level)
  - Low Stock (at or below minimum)
  - Out of Stock (zero quantity)
- **Sort**: Sort inventory by product name
- Real-time filtering with no page reload

### 3. **Barcode Lookup & Scanner**

- **Barcode Input**: Large, focused input field in Scanner tab
- **Instant Lookup**: Press Enter to search inventory
- **Product Details Display**: Shows full item information when found:
  - Product Name
  - SKU & Barcode
  - Category & Current Stock
  - Unit Price & Status
  - Minimum Stock Level
- **Error Handling**: Clear message if barcode not found
- **Focus Management**: Auto-focus on input for continuous scanning

### 4. **Dashboard & Analytics**

Real-time metrics that update when inventory changes:
- **Total Sales**: Current sales figure
- **Total Inventory**: Sum of all product stock quantities
- **Low Stock Items**: Count of items below minimum threshold
- **Out of Stock**: Count of items with zero quantity

#### Live Charts (Updated with actual inventory data)
- **Daily Sales**: Line chart showing sales trend
- **Sales by Category**: Doughnut chart with inventory distribution by category
- **Top Products**: Bar chart showing products with highest stock levels
- **Monthly Sales Trend**: Line chart showing sales progression

### 5. **Sales Reports**

- **Period Selection**: Daily, Weekly, or Monthly reports
- **Sales Trend Chart**: Line chart for selected period
- **Sales Distribution**: Pie chart showing category breakdown
- **Detailed Table**: Transaction-by-transaction sales records
- Charts update dynamically based on selected period

### 6. **Responsive Design**

- **Desktop**: Full 3-column layout with sidebar navigation
- **Tablet**: Optimized grid layouts
- **Mobile**: Single-column layout, touch-friendly buttons, collapsible elements
- Modern, polished UI with smooth animations
- Professional color scheme with visual hierarchy

## 🗄️ Data Persistence

All data is stored in **localStorage**:
- Inventory data persists across browser sessions
- No server or backend required
- Data remains even after closing the browser
- Clear browser cache to reset data

**Storage Key**: `inventoryData` (JSON array of products)

## 🎨 Design Highlights

- **Color Scheme**: Professional blue/purple gradient with semantic colors
- **Typography**: Segoe UI system fonts for modern appearance
- **Spacing**: 24px base grid for visual consistency
- **Shadows**: Layered shadows for depth perception
- **Animations**: Smooth fade-in and slide-in transitions
- **Icons**: Font Awesome for recognizable visual cues

## 🚀 Quick Start

1. **Open in Browser**
   ```
   Open index.html in any modern web browser
   ```

2. **Add Your First Item**
   - Click "Dashboard" to view metrics
   - Navigate to "Inventory Management"
   - Click "Add New Item"
   - Fill in product details
   - Save (data persists in localStorage)

3. **Scan/Lookup Barcode**
   - Go to "Barcode Scanner" tab
   - Scan barcode or type manually
   - Press Enter to lookup
   - View full product details instantly

4. **View Analytics**
   - Check dashboard for live metrics
   - Review "Sales Reports" for detailed analytics
   - Charts update automatically when inventory changes

## 📋 Product Schema

```javascript
{
  sku: "SKU001",              // Unique stock keeping unit
  name: "Wireless Headphones", // Product name
  barcode: "1234567890123",   // Unique barcode (for scanning)
  category: "Electronics",     // Category for filtering
  stock: 45,                   // Current quantity in stock
  price: 129.99,              // Unit price
  minStock: 10                // Threshold for low stock alert
}
```

## 🛠️ Technical Stack

- **HTML5**: Semantic markup
- **CSS3**: Modern styling with CSS variables, Grid, Flexbox
- **JavaScript**: Vanilla JS (no dependencies)
- **Chart.js**: Interactive charts and analytics
- **Font Awesome**: Professional icons
- **LocalStorage API**: Client-side data persistence

## 📱 Browser Support

- Chrome/Chromium ✅
- Firefox ✅
- Safari ✅
- Edge ✅
- Mobile browsers ✅

## 🎯 Use Cases

- **Small Retail Stores**: Manage inventory and sales
- **Warehouses**: Track stock levels and products
- **E-commerce**: Barcode-based product management
- **Inventory Audits**: Quick product lookups via barcode
- **Stock Alerts**: Visual indicators for low and out-of-stock items

## 🔒 Data Management

### How to Backup Data
Open browser DevTools → Application → LocalStorage → Copy `inventoryData` value

### How to Restore Data
1. Go to DevTools → Application → LocalStorage
2. Click on your domain
3. Create new key: `inventoryData`
4. Paste backed-up JSON value

### How to Reset Data
- DevTools → Application → LocalStorage → Delete `inventoryData`
- Or: Settings → Clear browsing data → Cookies and other site data

## 🚀 Future Enhancements

- ✨ Add user authentication (login/register)
- 📦 Export inventory to CSV/Excel
- 🔗 API integration for backend sync
- 📊 Advanced reporting with date ranges
- 🏷️ Barcode generation for new items
- 📱 Mobile app (React Native/Flutter)
- 🔔 Email alerts for low stock
- 👥 Multi-user support with roles
- 🌙 Dark mode toggle
- 🗂️ Multiple warehouse support

## 💡 Tips & Tricks

1. **Use unique barcodes** - EAN-13, UPC, or any unique identifier
2. **Set realistic min stock** - Helps prevent stockouts
3. **Regular backups** - Export data periodically via DevTools
4. **Keyboard shortcuts** - Tab through forms, Enter to submit
5. **Search tips** - Search by partial name or barcode for quick lookup

## ❓ FAQ

**Q: Does data sync across devices?**
A: No, data is stored locally. Use export/import or integrate with backend API.

**Q: Can I delete all data?**
A: Yes, clear browser storage or right-click inventory → Developer Tools → Clear LocalStorage.

**Q: Is there a limit to inventory items?**
A: No practical limit. LocalStorage typically allows ~5-10MB of data.

**Q: Can I edit barcodes?**
A: Yes, but unique barcodes are enforced to prevent duplicates.

**Q: Do charts update in real-time?**
A: Yes! Charts refresh automatically when inventory changes.

## 📞 Support

For issues or feature requests, check the code comments and error console (F12 DevTools).

---

**Version**: 1.0.0  
**Last Updated**: December 2024  
**License**: MIT
