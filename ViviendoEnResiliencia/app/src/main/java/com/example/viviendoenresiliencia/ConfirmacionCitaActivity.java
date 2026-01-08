package com.example.viviendoenresiliencia;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;
import org.json.JSONObject;

import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

public class ConfirmacionCitaActivity extends AppCompatActivity {

    private static final String HORARIO_API_BASE_URL = "http://192.168.100.239/proyectofinalMW";

    private RequestQueue requestQueue;
    private TextView tvTipoSesion, tvFecha, tvHora;
    private Button btnConfirmarCita, btnCancelar;

    // Variables de la Cita
    private String idUsuario;
    private String fechaHoraAPI; // Formato YYYY-MM-DD HH:MM:00
    private String tipoSesion;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_confirmacion_cita);

        // Inicialización de Volley
        requestQueue = Volley.newRequestQueue(this);

        // Inicialización de vistas
        tvTipoSesion = findViewById(R.id.tv_tipo_sesion);
        tvFecha = findViewById(R.id.tv_fecha);
        tvHora = findViewById(R.id.tv_hora);
        btnConfirmarCita = findViewById(R.id.btn_confirmar_cita);
        btnCancelar = findViewById(R.id.btn_cancelar);

        // 1. Obtener datos del Intent
        if (!getAppointmentData(getIntent())) {
            Toast.makeText(this, "Error: Faltan datos de la cita.", Toast.LENGTH_LONG).show();
            finish();
            return;
        }

        // 2. Mostrar datos en la UI
        displayAppointmentData();

        // 3. Configurar listeners
        setupConfirmarButton();
        setupCancelarButton();
    }

    private boolean getAppointmentData(Intent intent) {
        idUsuario = intent.getStringExtra(CitasActivity.EXTRA_ID_USUARIO);
        fechaHoraAPI = intent.getStringExtra(CitasActivity.EXTRA_FECHA_HORA);
        tipoSesion = intent.getStringExtra(CitasActivity.EXTRA_TIPO_SESION);

        return idUsuario != null && fechaHoraAPI != null && tipoSesion != null;
    }

    private void displayAppointmentData() {
        tvTipoSesion.setText("Tipo de Sesión: " + tipoSesion);

        // Formatear la fecha y hora para el display (más amigable)
        try {
            SimpleDateFormat apiFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.US);
            Date date = apiFormat.parse(fechaHoraAPI);

            // Formato de fecha para el display (Ej: Lunes 14 de Octubre)
            SimpleDateFormat dateFormat = new SimpleDateFormat("EEEE d 'de' MMMM", new Locale("es", "ES"));
            // Formato de hora para el display (Ej: 09:00 AM)
            // Nota: Se usa Locale.getDefault() para respetar la configuración local del sistema
            SimpleDateFormat timeFormat = new SimpleDateFormat("hh:mm a", Locale.getDefault());

            tvFecha.setText("Fecha: " + dateFormat.format(date));
            tvHora.setText("Hora: " + timeFormat.format(date));

        } catch (ParseException e) {
            Log.e("ConfirmacionCita", "Error al parsear la fecha: " + fechaHoraAPI, e);
            tvFecha.setText("Fecha: (Error de formato)");
            tvHora.setText("Hora: (Error de formato)");
        }
    }

    // ==============================================================================
    // Lógica de Agenda (Volley POST)
    // ==============================================================================

    private void setupConfirmarButton() {
        btnConfirmarCita.setOnClickListener(v -> {

            JSONObject jsonBody = new JSONObject();
            try {
                jsonBody.put("idusuario", idUsuario);
                jsonBody.put("fecha", fechaHoraAPI);
                jsonBody.put("tipo_sesion", tipoSesion);
            } catch (JSONException e) {
                Toast.makeText(ConfirmacionCitaActivity.this, "Error de datos: " + e.getMessage(), Toast.LENGTH_SHORT).show();
                return;
            }

            String url = HORARIO_API_BASE_URL + "/citas";

            btnConfirmarCita.setEnabled(false);
            btnConfirmarCita.setText("Agendando...");

            JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(
                    Request.Method.POST,
                    url,
                    jsonBody,
                    new Response.Listener<JSONObject>() {
                        @Override
                        public void onResponse(JSONObject response) {
                            Toast.makeText(ConfirmacionCitaActivity.this, "Cita agendada con éxito.", Toast.LENGTH_LONG).show();

                            // *** CAMBIO CLAVE: Redireccionar a MainActivity y limpiar la pila ***
                            Intent intent = new Intent(ConfirmacionCitaActivity.this, MainActivity.class);
                            // Estas flags aseguran que todas las actividades previas (Citas y Confirmación) se cierren
                            intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
                            startActivity(intent);
                            finish(); // Termina la actividad actual
                        }
                    },
                    new Response.ErrorListener() {
                        @Override
                        public void onErrorResponse(VolleyError error) {
                            btnConfirmarCita.setEnabled(true);
                            btnConfirmarCita.setText("Confirmar Cita");

                            String errorMessage = "Error desconocido.";
                            if (error.networkResponse != null) {
                                try {
                                    String responseBody = new String(error.networkResponse.data, "utf-8");
                                    JSONObject data = new JSONObject(responseBody);
                                    errorMessage = data.optString("error", "Error del servidor: " + error.networkResponse.statusCode);
                                } catch (Exception e) {
                                    errorMessage = "Error de red o formato de respuesta inválido.";
                                }
                            } else if (error.getMessage() != null) {
                                errorMessage = "Error de conexión: " + error.getMessage();
                            }
                            Toast.makeText(ConfirmacionCitaActivity.this, errorMessage, Toast.LENGTH_LONG).show();
                            Log.e("CitasVolley", "Error al agendar cita", error);
                        }
                    }
            );

            requestQueue.add(jsonObjectRequest);
        });
    }

    private void setupCancelarButton() {
        btnCancelar.setOnClickListener(v -> {
            // Vuelve a CitasActivity
            finish();
        });
    }
}
