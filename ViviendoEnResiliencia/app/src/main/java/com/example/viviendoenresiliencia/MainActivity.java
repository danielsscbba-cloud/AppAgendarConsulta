package com.example.viviendoenresiliencia;

import android.content.Context;
import android.content.Intent; // Necesario para la navegación
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.MenuItem; // Necesario para el listener de navegación
import android.view.View;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;
import androidx.annotation.NonNull; // Necesario para el listener de navegación
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.Volley;
import com.google.android.material.bottomnavigation.BottomNavigationView; // Necesario para la barra de navegación

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

public class MainActivity extends AppCompatActivity {

    private static final String TAG = "MainActivity";
    private static final String CITAS_URL = "http://192.168.100.239/proyectofinalMW/citas/usuario?idusuario=";

    // Constantes de SharedPreferences
    private static final String PREF_NAME = "UserSessionPrefs";
    private static final String KEY_ID_USUARIO = "idUsuario";

    private LinearLayout citasContainer;
    private RequestQueue requestQueue;
    private Button btnCerrarSesion;
    private BottomNavigationView bottomNav; // Declarado como variable de instancia

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        citasContainer = findViewById(R.id.citas_container);
        requestQueue = Volley.newRequestQueue(this);

        btnCerrarSesion = findViewById(R.id.btnCerrarSesion);

        // 1. Configurar listener para el botón "Cerrar Sesión"
        btnCerrarSesion.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                cerrarSesion();
            }
        });

        // 1. Configurar la barra de navegación inferior
        bottomNav = findViewById(R.id.bottom_nav_view); // Inicialización
        bottomNav.setOnItemSelectedListener(this::onNavigationItemSelected);

        // La selección inicial se moverá a onResume para manejar el regreso,
        // pero se deja aquí si la Activity se crea por primera vez.
        bottomNav.setSelectedItemId(R.id.nav_inicio);

        // 2. Cargar las citas del usuario
        fetchUserCitas();

    }

    @Override
    protected void onResume() {
        super.onResume();
        // Cuando MainActivity regresa al primer plano (ej. después de volver de CitasActivity),
        // aseguramos que el ítem "Inicio" esté seleccionado en la barra de navegación.
        if (bottomNav != null) {
            bottomNav.setSelectedItemId(R.id.nav_inicio);
        }
    }

    /**
     * Maneja el cierre de sesión y la redirección a LoginActivity.
     */
    private void cerrarSesion() {
        // 1. Limpiar SharedPreferences
        SharedPreferences sharedPref = getSharedPreferences(PREF_NAME, Context.MODE_PRIVATE);
        SharedPreferences.Editor editor = sharedPref.edit();

        // Se elimina el ID de usuario para invalidar la sesión
        editor.remove(KEY_ID_USUARIO);
        editor.apply(); // Aplica los cambios de forma asíncrona

        // 2. Notificar al usuario
        Toast.makeText(this, "Sesión cerrada exitosamente.", Toast.LENGTH_SHORT).show();

        // 3. Redirigir a la pantalla de Login
        // FLAG_ACTIVITY_NEW_TASK y FLAG_ACTIVITY_CLEAR_TASK aseguran que el usuario
        // no pueda volver a esta actividad con el botón 'Atrás'.
        Intent intent = new Intent(MainActivity.this, LoginActivity.class); // Asegúrate de que LoginActivity.class es el nombre correcto
        intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);

        // 4. Finalizar la actividad actual
        finish();
    }



    private boolean onNavigationItemSelected(@NonNull MenuItem item) {
        int itemId = item.getItemId();

        if (itemId == R.id.nav_inicio) {
            // Ya estamos aquí, no hacemos nada
            return true;
        } else if (itemId == R.id.nav_citas) {
            // Navegar a la Activity de Citas
            // Importante: No llamar a finish() aquí para poder volver.
            Intent intent = new Intent(MainActivity.this, CitasActivity.class);
            startActivity(intent);
            return true;
        } else if (itemId == R.id.nav_notificaciones) {
            // Navegar a la Activity de Notificaciones
            // Asumiendo que existe una clase NotificacionesActivity.java
            Intent intent = new Intent(MainActivity.this, NotificacionesActivity.class);
            startActivity(intent);
            return true;
        }
        return false;
    }

    /**
     * Obtiene el ID del usuario logueado y realiza la solicitud a la API de citas.
     */
    private void fetchUserCitas() {
        SharedPreferences sharedPref = getSharedPreferences(PREF_NAME, Context.MODE_PRIVATE);
        String idUsuario = sharedPref.getString(KEY_ID_USUARIO, null);

        if (idUsuario == null) {
            Toast.makeText(this, "Error: Sesión no encontrada. Por favor, inicia sesión.", Toast.LENGTH_LONG).show();
            return;
        }

        String url = CITAS_URL + idUsuario;

        JsonArrayRequest jsonArrayRequest = new JsonArrayRequest(
                Request.Method.GET,
                url,
                null,
                response -> {
                    citasContainer.removeAllViews(); // Limpiar vistas antiguas
                    try {
                        if (response.length() == 0) {
                            TextView noCitas = new TextView(this);
                            noCitas.setText("No tienes citas agendadas por ahora.");
                            noCitas.setPadding(0, 32, 0, 32);
                            citasContainer.addView(noCitas);
                            return;
                        }

                        for (int i = 0; i < response.length(); i++) {
                            JSONObject citaJson = response.getJSONObject(i);
                            addCitaCard(
                                    citaJson.getString("fecha"),
                                    citaJson.getString("tipo_sesion")
                            );
                        }
                    } catch (JSONException e) {
                        Log.e(TAG, "Error al parsear el JSON de citas: " + e.getMessage());
                        Toast.makeText(this, "Error en los datos de citas recibidos.", Toast.LENGTH_LONG).show();
                    }
                },
                error -> {
                    Log.e(TAG, "Error de Volley al obtener citas: " + error.toString());
                    Toast.makeText(this, "Error de conexión o servidor al cargar citas.", Toast.LENGTH_LONG).show();
                }
        );

        requestQueue.add(jsonArrayRequest);
    }

    /**
     * Crea dinámicamente un CardView con los datos de la cita y lo añade al contenedor.
     */
    private void addCitaCard(String fecha, String tipoSesion) {

        // Convertir 12dp a píxeles para el margen
        int marginDp = 12;
        float density = getResources().getDisplayMetrics().density;
        int marginPixels = (int) (marginDp * density);

        // Creación del CardView
        CardView cardView = new CardView(this);
        LinearLayout.LayoutParams cardParams = new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT
        );
        cardParams.setMargins(0, 0, 0, marginPixels);
        cardView.setLayoutParams(cardParams);
        cardView.setRadius(12f);
        cardView.setCardElevation(4f);

        // Contenedor principal horizontal
        LinearLayout innerLayout = new LinearLayout(this);
        innerLayout.setLayoutParams(new LinearLayout.LayoutParams(
                LinearLayout.LayoutParams.MATCH_PARENT,
                LinearLayout.LayoutParams.WRAP_CONTENT
        ));
        innerLayout.setOrientation(LinearLayout.HORIZONTAL);
        innerLayout.setPadding(marginPixels, marginPixels, marginPixels, marginPixels); // Añadir padding
        innerLayout.setGravity(android.view.Gravity.CENTER_VERTICAL);

        // Contenedor de texto (vertical)
        LinearLayout textLayout = new LinearLayout(this);
        LinearLayout.LayoutParams textParams = new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1f);
        textLayout.setLayoutParams(textParams);
        textLayout.setOrientation(LinearLayout.VERTICAL);

        // Texto 1: Fecha y Hora
        TextView tvFechaHora = new TextView(this);
        tvFechaHora.setText(formatDate(fecha));
        tvFechaHora.setTextSize(14);
        tvFechaHora.setTextColor(getResources().getColor(android.R.color.darker_gray));
        textLayout.addView(tvFechaHora);

        // Texto 2: Tipo de Sesión
        TextView tvTipo = new TextView(this);
        tvTipo.setText(tipoSesion);
        tvTipo.setTextSize(16);
        tvTipo.setTypeface(null, android.graphics.Typeface.BOLD);
        textLayout.addView(tvTipo);

        // Añadir layouts al CardView
        innerLayout.addView(textLayout);

        // Agregar ImageView de perfil (simplificado con un icono)
        ImageView ivProfile = new ImageView(this);
        LinearLayout.LayoutParams imageParams = new LinearLayout.LayoutParams(
                (int) (48 * density),
                (int) (48 * density)
        );
        imageParams.setMarginStart(marginPixels);
        ivProfile.setLayoutParams(imageParams);
        ivProfile.setImageResource(R.drawable.ic_doctora_perfil); // Asegúrate de que este recurso exista
        ivProfile.setContentDescription("Icono de perfil");
        ivProfile.setScaleType(ImageView.ScaleType.CENTER_CROP);

        innerLayout.addView(ivProfile);

        cardView.addView(innerLayout);

        // Añadir el CardView completo al contenedor principal
        citasContainer.addView(cardView);
    }

    /**
     * Formatea la fecha de "yyyy-MM-dd HH:mm:ss" a una cadena legible.
     */
    private String formatDate(String dateString) {
        SimpleDateFormat inputFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault());
        SimpleDateFormat outputFormat = new SimpleDateFormat("dd 'de' MMMM, h:mm a", new Locale("es", "ES"));
        try {
            Date date = inputFormat.parse(dateString);
            return outputFormat.format(date);
        } catch (ParseException e) {
            Log.e(TAG, "Error al parsear fecha: " + dateString);
            return dateString;
        }
    }
}
