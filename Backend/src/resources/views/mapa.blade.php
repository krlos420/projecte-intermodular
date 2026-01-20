<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa de Tiendas</title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <style>
        body { font-family: sans-serif; padding: 20px; }
        /*Esto es para que el mapa se vea bien i he visto que si no le ponia una altura no se me mostraba el mapa*/
        #map { 
            height: 500px; 
            width: 100%; 
            border: 2px solid #333;
            border-radius: 5px;
        }
        .lista-productos {
            padding-left: 20px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

    <h1>Mapa de Stock</h1>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"> </script>
    
        <script>
        // Inicializo el mapa centrado en coordenadas de Madrid (aprox) con zoom 6
        var map = L.map('map').setView([40.4167, -3.70325], 6);

        // Añado la capa visual (tiles) de OpenStreetMap. Sin esto el mapa sale gris.
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        // Recibimos los datos limpios desde el controlador
        const puntos = @json($puntos);
        // Recorro cada tienda que me devuelve la base de datos
        puntos.forEach(punto => {
            // Verificamos que las coordenadas existan y no sean nulas
            if (punto.latitude != null && punto.length != null) {

                let htmlProductos = "";

                // Si la tienda tiene productos, monto los <li> dinámicamente
                if (punto.products && punto.products.length > 0) {
                    punto.products.forEach(producto => {
                        htmlProductos += `<li>${producto.name} - ${producto.price}€</li>`;
                    });
                } else {
                    htmlProductos = "<li>No hay stock disponible</li>";
                }

                // Creo el HTML dinámico que irá dentro del popup
                const contenido = `
                    <h3>${punto.name}</h3>
                    <p>${punto.direction}</p>
                    <hr>
                    <b>Productos:</b>
                    <ul class="lista-productos">
                        ${htmlProductos}
                    </ul>
                `;

                L.marker([punto.latitude, punto.length])

                    .addTo(map)
                    .bindPopup(contenido);
            }
        });
    </script>

</body>
</html>
            