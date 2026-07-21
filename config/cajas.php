<?php

return [
    'starter' => [
        'nombre' => 'Starter Drop', 'precio' => 2.99, 'color' => 'cyan', 'tier' => 'low',
        'imagen' => '/images/lootra_visual_pack/12_cases/case_starter.webp',
        'descripcion' => 'Accesorios y premios instantáneos para empezar.',
        'premios' => [
            ['nombre' => 'Sticker Pack Neon', 'valor' => 0.75, 'rareza' => 'comun', 'peso' => 30, 'imagen' => 'https://images.unsplash.com/photo-1561214115-f2f134cc4912?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Llavero Lootra', 'valor' => 1.25, 'rareza' => 'comun', 'peso' => 26, 'imagen' => 'https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Tarjeta digital €3', 'valor' => 3, 'rareza' => 'poco_comun', 'peso' => 22, 'imagen' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Auriculares compactos', 'valor' => 6.5, 'rareza' => 'raro', 'peso' => 14, 'imagen' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Altavoz Mini', 'valor' => 14, 'rareza' => 'epico', 'peso' => 7, 'imagen' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Smartwatch Fit', 'valor' => 45, 'rareza' => 'legendario', 'peso' => 1, 'imagen' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=500&q=80'],
        ],
    ],
    'gaming' => [
        'nombre' => 'Gaming Vault', 'precio' => 9.99, 'color' => 'purple', 'tier' => 'mid',
        'imagen' => '/images/lootra_visual_pack/12_cases/case_gaming.webp',
        'descripcion' => 'Periféricos, hardware y equipo para tu setup.',
        'premios' => [
            ['nombre' => 'Alfombrilla RGB', 'valor' => 4, 'rareza' => 'comun', 'peso' => 30, 'imagen' => 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Tarjeta gaming €8', 'valor' => 8, 'rareza' => 'poco_comun', 'peso' => 28, 'imagen' => 'https://images.unsplash.com/photo-1605901309584-818e25960a8f?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Ratón Gaming Pro', 'valor' => 18, 'rareza' => 'raro', 'peso' => 22, 'imagen' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Teclado mecánico', 'valor' => 35, 'rareza' => 'raro', 'peso' => 13, 'imagen' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Mando inalámbrico', 'valor' => 65, 'rareza' => 'epico', 'peso' => 6, 'imagen' => 'https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Consola Next Gen', 'valor' => 420, 'rareza' => 'legendario', 'peso' => 1, 'imagen' => 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=500&q=80'],
        ],
    ],
    'tech' => [
        'nombre' => 'Tech Pulse', 'precio' => 24.99, 'color' => 'blue', 'tier' => 'mid',
        'imagen' => '/images/lootra_visual_pack/12_cases/case_tech.webp',
        'descripcion' => 'Tecnología premium y dispositivos para el día a día.',
        'premios' => [
            ['nombre' => 'Cargador inalámbrico', 'valor' => 9, 'rareza' => 'comun', 'peso' => 29, 'imagen' => 'https://images.unsplash.com/photo-1622445275463-afa2ab738c34?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Powerbank 20K', 'valor' => 18, 'rareza' => 'poco_comun', 'peso' => 28, 'imagen' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Auriculares ANC', 'valor' => 45, 'rareza' => 'raro', 'peso' => 23, 'imagen' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Smartwatch Pro', 'valor' => 95, 'rareza' => 'raro', 'peso' => 13, 'imagen' => 'https://images.unsplash.com/photo-1546868871-af0de0ae72be?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Tablet 10 pulgadas', 'valor' => 220, 'rareza' => 'epico', 'peso' => 6, 'imagen' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Portátil ultraligero', 'valor' => 850, 'rareza' => 'legendario', 'peso' => 1, 'imagen' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=500&q=80'],
        ],
    ],
    'luxury' => [
        'nombre' => 'Luxury Black', 'precio' => 49.99, 'color' => 'amber', 'tier' => 'high',
        'imagen' => '/images/lootra_visual_pack/12_cases/case_luxury.webp',
        'descripcion' => 'Piezas exclusivas, diseño y experiencias premium.',
        'premios' => [
            ['nombre' => 'Cartera de piel', 'valor' => 18, 'rareza' => 'comun', 'peso' => 29, 'imagen' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Gafas polarizadas', 'valor' => 38, 'rareza' => 'poco_comun', 'peso' => 28, 'imagen' => 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Perfume de autor', 'valor' => 75, 'rareza' => 'raro', 'peso' => 23, 'imagen' => 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Reloj automático', 'valor' => 180, 'rareza' => 'raro', 'peso' => 13, 'imagen' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Escapada premium', 'valor' => 450, 'rareza' => 'epico', 'peso' => 6, 'imagen' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=500&q=80'],
            ['nombre' => 'Reloj de lujo', 'valor' => 1800, 'rareza' => 'legendario', 'peso' => 1, 'imagen' => 'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=500&q=80'],
        ],
    ],
];
