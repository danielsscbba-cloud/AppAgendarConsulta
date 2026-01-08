# 📘 README

## Descripción General

Este proyecto corresponde a un **sistema multiplataforma** compuesto por una **página web**, una **aplicación móvil Android** y un **backend centralizado mediante una API REST**, desarrollada en PHP. Tanto la web como la aplicación móvil consumen los mismos endpoints, permitiendo una arquitectura modular, escalable y reutilizable.

El sistema está diseñado bajo una **arquitectura en capas**, separando claramente la presentación, la lógica de negocio y el acceso a datos.

---

## 🧱 Arquitectura del Sistema

El sistema está conformado por los siguientes componentes principales:

* **Frontend Web**: Página web desarrollada en PHP.
* **Aplicación Móvil**: App Android desarrollada en Java usando Android Studio.
* **Backend**: API REST desarrollada en PHP.
* **Base de Datos**: MySQL.

Ambos clientes (web y móvil) se comunican exclusivamente con la API mediante peticiones HTTP.

---

## 🔁 Diagrama de Arquitectura (Flowchart)

La siguiente arquitectura describe el flujo de comunicación entre los distintos componentes del sistema:

* La **Página Web** y la **App Móvil** envían solicitudes a la **API REST**.
* La API tiene como punto de entrada el archivo `index.php`.
* `index.php` enruta las solicitudes hacia los controladores correspondientes.
* Los controladores acceden a la capa de datos.
* La capa de datos se conecta a la base de datos MySQL.

---

## ⚙️ Backend – API REST

### Punto de Entrada

* **index.php**

  * Actúa como puerta de entrada a la API.
  * Recibe las peticiones HTTP.
  * Determina el controlador y el endpoint a ejecutar.

### Controladores

La lógica de negocio se organiza mediante controladores independientes:

* **Controlador Admin**: Gestión de funcionalidades administrativas.
* **Controlador Usuario**: Manejo de usuarios y autenticación.
* **Controlador Citas**: Administración de citas.
* **Controlador Horario**: Gestión de horarios.

Cada controlador se encarga de procesar las solicitudes, validar datos y comunicarse con la capa de datos.

---

## 🗄️ Capa de Datos

La interacción con la base de datos se maneja mediante una capa de abstracción compuesta por:

* **ConexionDB**

  * Clase encargada de establecer la conexión con la base de datos MySQL.

* **Clase CRUD**

  * Implementa las operaciones básicas:

    * Create
    * Read
    * Update
    * Delete

* **Consultas Personalizadas**

  * Métodos adicionales para consultas específicas que no se cubren con el CRUD estándar.

Esta separación permite mantener el código más limpio, reutilizable y fácil de mantener.

---

## Base de Datos

* **Motor**: MySQL
* Centraliza toda la información del sistema.
* Accedida únicamente a través de la capa de datos del backend.

---

## 📱 Aplicación Móvil

* Desarrollada en **Java** usando **Android Studio**.
* Consume la API REST mediante peticiones HTTP.
* Comparte la misma lógica de negocio que la versión web.

---

## 🌐 Página Web

* Desarrollada en **PHP**.
* Consume la API REST para todas las operaciones.
* No accede directamente a la base de datos.

---

## ✅ Ventajas de la Arquitectura

* Separación clara de responsabilidades.
* Reutilización de la lógica de negocio.
* Escalabilidad para nuevos clientes (por ejemplo, otra app).
* Mantenimiento más sencillo.
* Comunicación centralizada mediante API.

---

## 📌 Tecnologías Utilizadas

* PHP
* API REST
* Java (Android)
* Android Studio
* MySQL
* Arquitectura en capas

---

## 📄 Notas Finales

Este proyecto sigue buenas prácticas de desarrollo, permitiendo una evolución ordenada del sistema y facilitando su comprensión tanto a nivel académico como profesional.
