// ============================================================
// db-loader.js — Universal DB Product Loader
// Usage: <script src="Js/db-loader.js" data-subcategory="T-Shirts"></script>
// ============================================================

(function() {
  var scriptTag = document.currentScript;
  var subcategory = scriptTag ? scriptTag.getAttribute('data-subcategory') : null;
  if (!subcategory) return;

  var API = 'api/get_products.php?subcategory=' + encodeURIComponent(subcategory);

  fetch(API)
    .then(function(res) { return res.json(); })
    .then(function(products) {
      if (!products || products.length === 0) return;

      var grid = document.querySelector('.product-grid');
      if (!grid) return;

      products.forEach(function(p) {

        var sub = p.subcategory || subcategory;
        var stock = parseInt(p.stock) || 0;        // ✅ stock from DB
        var isOutOfStock = stock <= 0;

        var defaultSizes;
        if (sub === 'Jeans') {
          defaultSizes = ['30','32','34','36'];
        } else if (sub === 'Mens Shoes') {
          defaultSizes = ['6','7','8','9','10'];
        } else if (sub === 'Womens Shoes') {
          defaultSizes = ['35','36','37','38','39'];
        } else if (sub === 'Womens Heels') {
          defaultSizes = ['35','36','37','38','39'];
        } else if (sub === 'Kids Footwear') {
          defaultSizes = ['28','29','30','31','32'];
        } else {
          defaultSizes = ['S','M','L','XL'];
        }

        var sizes = p.sizes ? p.sizes.split(',').map(function(s){ return s.trim(); }) : defaultSizes;
        var img   = p.image_url ? p.image_url : '';
        var id    = 'db_' + p.product_id;
        var price = parseFloat(p.price) || 0;
        var desc  = p.description || '';

        // Build size pills — disabled if out of stock
        var sizePills = sizes.map(function(s) {
          if (isOutOfStock) {
            return '<button class="sz-pill" disabled style="opacity:0.4;cursor:not-allowed;">' + s + '</button>';
          }
          return '<button class="sz-pill" onclick="' +
            'this.parentElement.querySelectorAll(\'.sz-pill\').forEach(function(b){b.classList.remove(\'active\')});' +
            'this.classList.add(\'active\');' +
            'this.closest(\'.product-card\').dataset.size=\'' + s + '\'">' + s + '</button>';
        }).join('');

        var addFn = 'dbAdd_' + p.product_id;
        var buyFn = 'dbBuy_' + p.product_id;

        window[addFn] = function(sz) {
          var cart = JSON.parse(localStorage.getItem('cart') || '[]');
          var ex = cart.find(function(i){ return i.id === id && i.size === sz; });

          if (ex) {
            // ✅ Block if already at stock limit
            if (ex.quantity >= stock) {
              if (typeof showStockToast === 'function') {
                showStockToast(p.product_name, stock);
              } else {
                alert('Only ' + stock + ' in stock for ' + p.product_name + '!');
              }
              return;
            }
            ex.quantity = (ex.quantity || 1) + 1;
          } else {
            cart.push({
              id: id,
              name: p.product_name,
              price: price,
              img: img,
              image: img,
              quantity: 1,
              size: sz,
              subcategory: sub,
              stock: stock   // ✅ save stock so cart.html can enforce limit
            });
          }

          localStorage.setItem('cart', JSON.stringify(cart));
          if (typeof updateAllCartCounts === 'function') updateAllCartCounts();
          if (typeof showCartToast === 'function') {
            showCartToast(p.product_name);
          } else {
            alert(p.product_name + ' added to cart!');
          }
        };

        window[buyFn] = function(sz) {
          window[addFn](sz);
          window.location.href = 'checkout.html';
        };

        // ✅ Buttons: Out of Stock vs Normal
        var buttonsHTML;
        if (isOutOfStock) {
          buttonsHTML =
            '<button class="btn-cart" disabled style="flex:1;padding:10px 6px;border:2px solid #ccc;background:#f5f5f5;color:#999;font-size:11.5px;font-weight:700;letter-spacing:.5px;border-radius:10px;cursor:not-allowed;text-transform:uppercase;">Out of Stock</button>';
        } else {
          buttonsHTML =
            '<button class="btn-cart" onclick="' +
              'var sz=this.closest(\'.product-card\').dataset.size;' +
              'if(!sz){alert(\'Please select a size!\');return;}' +
              addFn + '(sz)">Add to Cart</button>' +
            '<button class="btn-buy" onclick="' +
              'var sz=this.closest(\'.product-card\').dataset.size;' +
              'if(!sz){alert(\'Please select a size!\');return;}' +
              buyFn + '(sz)">Buy Now</button>';
        }

        // ✅ Stock badge on image
        var stockBadge = isOutOfStock
          ? '<div style="position:absolute;top:10px;left:10px;background:#e74c3c;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:5px;letter-spacing:.5px;">OUT OF STOCK</div>'
          : (stock <= 5 ? '<div style="position:absolute;top:10px;left:10px;background:#f39c12;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:5px;letter-spacing:.5px;">ONLY ' + stock + ' LEFT</div>' : '');

        var card = document.createElement('div');
        card.className = 'product-card';
        if (isOutOfStock) card.style.opacity = '0.75';

        card.innerHTML =
          '<div class="card-img-wrap" style="position:relative;">' +
            (img ? '<img src="' + img + '" alt="' + p.product_name + '" onerror="this.style.display=\'none\'">' : '') +
            stockBadge +
          '</div>' +
          '<div class="card-body">' +
            '<h4>' + p.product_name + '</h4>' +
            '<p>' + desc + '</p>' +
            '<div class="card-price">&#8377;' + price.toLocaleString('en-IN') + '</div>' +
            '<span class="size-label">Select Size</span>' +
            '<div class="sz-row">' + sizePills + '</div>' +
            '<div class="card-btn-row">' + buttonsHTML + '</div>' +
          '</div>';

        grid.appendChild(card);
      });
    })
    .catch(function(e) {
      console.log('DB products could not be loaded:', e);
    });
})();