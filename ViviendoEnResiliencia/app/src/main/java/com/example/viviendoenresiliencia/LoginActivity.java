package com.example.viviendoenresiliencia;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
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

public class LoginActivity extends AppCompatActivity {

    // Constantes para SharedPreferences (Buenas Prácticas)
    private static final String PREF_NAME = "UserSessionPrefs";
    private static final String KEY_LOGGED_IN = "isLoggedIn";
    private static final String KEY_ID_USUARIO = "idUsuario";
    private static final String KEY_USERNAME = "username";
    private static final String KEY_NOMBRE = "nombre"; // Para usar en mensajes de bienvenida

    private static final String TAG = "LoginActivity";
    private static final String LOGIN_URL = "http://192.168.100.239/proyectofinalMW/usuarios/login";

    // ... UI Components and RequestQueue declarations remain the same ...
    private EditText etCorreo;
    private EditText etPassword;
    private Button btnEntrar;
    private Button btnCrearCuenta;
    private RequestQueue requestQueue;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        requestQueue = Volley.newRequestQueue(this);

        // ... findViewByID calls remain the same ...
        etCorreo = findViewById(R.id.et_correo);
        etPassword = findViewById(R.id.et_password);
        btnEntrar = findViewById(R.id.btn_entrar);
        btnCrearCuenta = findViewById(R.id.btn_crear_cuenta);

        // Acción: Redirigir a la pantalla de Crear Cuenta
        btnCrearCuenta.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                Intent intent = new Intent(LoginActivity.this, CrearCuentaActivity.class);
                startActivity(intent);
            }
        });

        // Acción: Manejar el intento de Login
        btnEntrar.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                attemptLogin();
            }
        });
    }

    /**
     * Attempts to log in and processes the API response.
     */
    private void attemptLogin() {
        // ... (input validation and JSON body creation remain the same) ...
        final String correo = etCorreo.getText().toString().trim();
        final String password = etPassword.getText().toString().trim();

        if (correo.isEmpty() || password.isEmpty()) {
            Toast.makeText(this, "Por favor, introduce tu correo/usuario y contraseña.", Toast.LENGTH_LONG).show();
            return;
        }

        JSONObject jsonBody = new JSONObject();
        try {
            jsonBody.put("correo", correo);
            jsonBody.put("password", password);
        } catch (JSONException e) {
            Log.e(TAG, "Error creando cuerpo JSON: " + e.getMessage());
            return;
        }

        // Crear la solicitud POST de Volley
        JsonObjectRequest jsonObjectRequest = new JsonObjectRequest(
                Request.Method.POST,
                LOGIN_URL,
                jsonBody,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {
                        Log.d(TAG, "Respuesta de Login: " + response.toString());
                        try {
                            if (response.has("error")) {
                                // Login fallido
                                String errorMessage = response.getString("error");
                                Toast.makeText(LoginActivity.this, "Error: " + errorMessage, Toast.LENGTH_LONG).show();

                            } else {
                                // Login exitoso - GUARDAR DATOS

                                // 🔑 Llamada clave para guardar los datos del usuario
                                saveUserData(response);

                                String username = response.getString(KEY_USERNAME);
                                Toast.makeText(LoginActivity.this, "¡Bienvenido, " + username + "!", Toast.LENGTH_SHORT).show();

                                navigateToMainActivity();
                            }

                        } catch (JSONException e) {
                            Log.e(TAG, "Error al parsear la respuesta JSON: " + e.getMessage());
                            Toast.makeText(LoginActivity.this, "Respuesta inválida o incompleta del servidor.", Toast.LENGTH_LONG).show();
                        }
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        // ... (Error handling remains the same) ...
                        Log.e(TAG, "Error de Volley: " + error.toString());
                        String errorMessage = "Error de conexión. Verifica tu red o la dirección del servidor.";
                        if (error.networkResponse != null) {
                            errorMessage = "Error de servidor. Código: " + error.networkResponse.statusCode;
                        }
                        Toast.makeText(LoginActivity.this, errorMessage, Toast.LENGTH_LONG).show();
                    }
                }
        );
        requestQueue.add(jsonObjectRequest);
    }

    /**
     * Guarda los datos del usuario en SharedPreferences.
     */
    private void saveUserData(JSONObject userObject) throws JSONException {
        // Obtiene la instancia de SharedPreferences
        SharedPreferences sharedPref = getSharedPreferences(PREF_NAME, Context.MODE_PRIVATE);
        // Obtiene un editor para realizar cambios
        SharedPreferences.Editor editor = sharedPref.edit();

        // 1. Guardar el estado de login
        editor.putBoolean(KEY_LOGGED_IN, true);

        // 2. Guardar los datos específicos del usuario
        editor.putString(KEY_ID_USUARIO, userObject.getString("idusuario"));
        editor.putString(KEY_USERNAME, userObject.getString("username"));
        editor.putString(KEY_NOMBRE, userObject.getString("nombre"));
        // Opcional: puedes guardar otros campos como el teléfono, si son necesarios
        // editor.putString("telefono", userObject.getString("telefono"));

        // Aplica los cambios de forma asíncrona
        editor.apply();
        Log.d(TAG, "Datos de usuario guardados correctamente en SharedPreferences.");
    }


    /**
     * Navigates to the MainActivity and clears the activity stack.
     */
    private void navigateToMainActivity() {
        Intent intent = new Intent(LoginActivity.this, MainActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        finish();
    }
}
