package com.example.viviendoenresiliencia;

import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.regex.Pattern;

public class CrearCuentaActivity extends AppCompatActivity {

    private static final String TAG = "CrearCuentaActivity";
    // Endpoint para crear usuarios
    private static final String REGISTRO_URL = "http://192.168.100.239/proyectofinalMW/usuarios";

    // Componentes de la UI
    // IDs ajustados para coincidir con el XML
    private EditText etNombre;
    private EditText etUsername;
    private EditText etCorreo;
    private EditText etPassword;
    private EditText etTelefono;
    private Button btnFinalizarRegistro;

    private RequestQueue requestQueue;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_crear_cuenta); // Asegúrate de que este layout exista

        // Inicialización de Volley
        requestQueue = Volley.newRequestQueue(this);

        // Inicialización de Vistas - AHORA USANDO LOS IDS CORRECTOS DEL XML
        etNombre = findViewById(R.id.et_nombre_registro); // ID ajustado
        etUsername = findViewById(R.id.et_username_registro); // ID ajustado
        etCorreo = findViewById(R.id.et_correo_registro); // ID ajustado
        etPassword = findViewById(R.id.et_password_registro); // ID ajustado
        etTelefono = findViewById(R.id.et_telefono_registro); // ID ajustado
        btnFinalizarRegistro = findViewById(R.id.btn_finalizar_registro);

        btnFinalizarRegistro.setOnClickListener(v -> attemptRegistro());
    }

    /**
     * Valida los campos de entrada y llama al API para registrar el usuario.
     */
    private void attemptRegistro() {
        final String nombre = etNombre.getText().toString().trim();
        final String username = etUsername.getText().toString().trim();
        final String correo = etCorreo.getText().toString().trim();
        final String password = etPassword.getText().toString().trim();
        final String telefono = etTelefono.getText().toString().trim();

        // 1. Validación de campos vacíos
        if (nombre.isEmpty() || username.isEmpty() || correo.isEmpty() || password.isEmpty() || telefono.isEmpty()) {
            Toast.makeText(this, "Por favor, completa todos los campos.", Toast.LENGTH_LONG).show();
            return;
        }

        // 2. Validación de formato de correo simple
        if (!android.util.Patterns.EMAIL_ADDRESS.matcher(correo).matches()) {
            Toast.makeText(this, "Introduce un correo electrónico válido.", Toast.LENGTH_LONG).show();
            return;
        }

        // 3. Validación básica de longitud de contraseña
        if (password.length() < 6) {
            Toast.makeText(this, "La contraseña debe tener al menos 6 caracteres.", Toast.LENGTH_LONG).show();
            return;
        }

        // 4. Crear el cuerpo JSON para la solicitud
        JSONObject jsonBody = new JSONObject();
        try {
            jsonBody.put("nombre", nombre);
            jsonBody.put("username", username);
            jsonBody.put("correo_electronico", correo);
            jsonBody.put("password", password);
            jsonBody.put("telefono", telefono);
        } catch (JSONException e) {
            Log.e(TAG, "Error creando cuerpo JSON: " + e.getMessage());
            return;
        }

        // 5. Crear la solicitud POST de Volley
        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(
                Request.Method.POST,
                REGISTRO_URL,
                jsonBody,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {
                        Log.d(TAG, "Respuesta de Registro: " + response.toString());
                        try {
                            if (response.has("error")) {
                                // Registro fallido (ej. usuario o correo ya existe)
                                String errorMessage = response.getString("error");
                                Toast.makeText(CrearCuentaActivity.this, "Error de Registro: " + errorMessage, Toast.LENGTH_LONG).show();
                            } else {
                                // Registro exitoso
                                Toast.makeText(CrearCuentaActivity.this, "Cuenta creada con éxito. Por favor, inicia sesión.", Toast.LENGTH_LONG).show();
                                // Regresar a la pantalla de Login
                                finish();
                            }
                        } catch (JSONException e) {
                            Log.e(TAG, "Error al parsear la respuesta JSON: " + e.getMessage());
                            Toast.makeText(CrearCuentaActivity.this, "Respuesta inválida o incompleta del servidor.", Toast.LENGTH_LONG).show();
                        }
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        Log.e(TAG, "Error de Volley en registro: " + error.toString());
                        String errorMessage = "Error de conexión. Verifica tu red o la dirección del servidor.";
                        if (error.networkResponse != null) {
                            errorMessage = "Error de servidor. Código: " + error.networkResponse.statusCode;
                        }
                        Toast.makeText(CrearCuentaActivity.this, errorMessage, Toast.LENGTH_LONG).show();
                    }
                }
        );
        requestQueue.add(jsonObjectRequest);
    }
}
