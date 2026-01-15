{*
  Template file for synchronizing product data with Google Content API.
  Assumes variables for product data are passed to this template.
*}

{# Loop through each product #}
{% for product in products %}
  {# Start of a product block #}
  {
    "offerId": "{{ product.offer_id }}",
    "title": "{{ product.name|escape('json') }}",
    "description": "{{ product.description|escape('json') }}",
    "link": "{{ product.url }}",
    "imageLink": "{{ product.image_url }}",
    "contentLanguage": "{{ product.language }}",
    "targetCountry": "{{ product.target_country }}",
    "price": "{{ product.price }} {{ product.currency }}",
    "availability": "{{ product.availability }}",
    "condition": "{{ product.condition }}",
    "gtin": "{{ product.gtin }}",
    "brand": "{{ product.brand }}",
    "mpn": "{{ product.mpn }}",
    "googleProductCategory": "{{ product.google_category }}",
    "additionalImageLinks": [
      {% for additional_image in product.additional_images %}
        "{{ additional_image }}"
        {% if not loop.last %},{% endif %}
      {% endfor %}
    ],
    "shipping": [
      {
        "country": "{{ product.shipping_country }}",
        "service": "{{ product.shipping_service }}",
        "price": "{{ product.shipping_price }} {{ product.currency }}"
      }
    ]
  }
  {# End of a product block #}
  {% if not loop.last %},{% endif %}
{% endfor %}
