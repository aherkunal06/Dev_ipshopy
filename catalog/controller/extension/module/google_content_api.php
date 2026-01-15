from flask import Flask, render_template, request
import requests
import json

app = Flask(__name__)

# Replace with your actual Google Content API credentials
API_KEY = 'your_api_key'
BASE_URL = 'https://www.googleapis.com/content/v2/'

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/products')
def list_products():
    endpoint = f'{BASE_URL}products'
    headers = {
        'Authorization': f'Bearer {API_KEY}',
        'Accept': 'application/json'
    }
    response = requests.get(endpoint, headers=headers)
    products = response.json().get('products', [])
    return render_template('products.html', products=products)

if __name__ == '__main__':
    app.run(debug=True)
