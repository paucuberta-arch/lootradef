@extends('layouts.app')

@section('title','Lootra')

@section('styles')

    <style>

        .hero{

            height:85vh;

            display:flex;
            align-items:center;
            justify-content:center;

            text-align:center;

            background:
                    radial-gradient(circle at top,#8B5CF620,transparent 40%);

        }

        .hero h1{

            font-size:72px;

            margin-bottom:20px;

        }

        .hero p{

            color:#9CA3AF;

            font-size:22px;

            margin-bottom:45px;

        }


    </style>

@endsection


@section('content')

    <section class="hero">

        <div class="hero-glow glow1"></div>
        <div class="hero-glow glow2"></div>

        <div class="hero-content">

        <span class="badge">
            ✨ Nueva experiencia Lootra
        </span>

            <h1>
                Abre cajas.<br>
                Consigue premios increíbles.
            </h1>

            <p>

                Descubre una nueva forma de abrir cajas con una experiencia
                moderna, transparente y diseñada para jugadores.

            </p>

            <div class="hero-buttons">

                <a href="#" class="btn-primary">

                    Explorar cajas

                </a>

                <a href="#" class="btn-secondary">

                    Ver premios

                </a>

            </div>

        </div>

    </section>

@endsection