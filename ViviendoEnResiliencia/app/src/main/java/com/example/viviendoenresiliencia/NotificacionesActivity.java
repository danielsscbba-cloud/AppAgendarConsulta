package com.example.viviendoenresiliencia;

import android.content.Context;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import org.json.JSONArray;
import org.json.JSONObject;
import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.List;
import java.text.SimpleDateFormat;
import java.util.Locale;
import java.util.Date;
import java.util.TimeZone;
import android.widget.ImageView; // Importación para ImageView

public class NotificacionesActivity extends AppCompatActivity {

    private static final String API_URL_BASE = "http://192.168.100.239/proyectofinalMW/citas/general/usuario?idusuario=";
    private static String ID_USUARIO = "";
    private static final String SHARED_PREFS_NAME = "UserSessionPrefs";
    private SharedPreferences sharedPref;
    private static final String KEY_ID_USUARIO = "idUsuario";


    private RecyclerView recyclerView;
    private NotificationAdapter adapter;
    private final Handler mainHandler = new Handler(Looper.getMainLooper());
    private BottomNavigationView bottomNav; // Declarado como variable de instancia

    // --- Clase de Modelo de Datos (Cita) ---
    public static class Cita {
        String id;
        String fecha;
        String tipoSesion;
        String estado;

        public Cita(String id, String fecha, String tipoSesion, String estado) {
            this.id = id;
            this.fecha = fecha;
            this.tipoSesion = tipoSesion;
            this.estado = estado;
        }
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        // Asegúrate de que el layout (activity_notificaciones.xml) contenga:
        // 1. Un RecyclerView con id @+id/recycler_view_notificaciones
        // 2. Un BottomNavigationView con id @+id/bottom_nav_view
        setContentView(R.layout.activity_notificaciones);

        // Inicialización de la navegación y el listener
        setupBottomNavigationInit();

        setupRecyclerView();
        sharedPref = getSharedPreferences(SHARED_PREFS_NAME, Context.MODE_PRIVATE);
        ID_USUARIO = sharedPref.getString(KEY_ID_USUARIO, null);

        // 3. Iniciar la carga de datos
        fetchCitas();
    }

    /**
     * Este método asegura que al regresar a esta Activity
     * (ya sea por el botón Atrás o por FLAG_ACTIVITY_REORDER_TO_FRONT),
     * el ítem 'Notificaciones' se marque.
     */
    @Override
    protected void onResume() {
        super.onResume();
        if (bottomNav != null) {
            // Se asegura de que se marque la pestaña correcta (Notificaciones)
            // Asume que R.id.nav_notificaciones está definido en tu menú.
            bottomNav.setSelectedItemId(R.id.nav_notificaciones);

        }
    }

    private void setupRecyclerView() {
        recyclerView = findViewById(R.id.recycler_view_notificaciones);
        recyclerView.setLayoutManager(new LinearLayoutManager(this));
        // El adaptador se creará y asignará después de cargar los datos
    }

    // --- Lógica de Red para obtener citas ---
    private void fetchCitas() {
        if (ID_USUARIO == null) {
            Log.e("Notificaciones", "ID de usuario no encontrado. No se pueden cargar citas.");
            // Si no hay ID, actualizamos la UI con una lista vacía
            updateUI(new ArrayList<>());
            return;
        }

        new Thread(() -> {
            // 1. Declara e inicializa la variable SIN 'final'.
            List<Cita> resultCitas = new ArrayList<>();
            String apiUrl = API_URL_BASE + ID_USUARIO;

            try {
                URL url = new URL(apiUrl);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");

                int responseCode = conn.getResponseCode();
                if (responseCode == HttpURLConnection.HTTP_OK) {
                    BufferedReader in = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                    String inputLine;
                    StringBuilder response = new StringBuilder();
                    while ((inputLine = in.readLine()) != null) {
                        response.append(inputLine);
                    }
                    in.close();

                    // 2. Asigna el resultado del parseo a la variable existente.
                    resultCitas = parseCitasJson(response.toString());

                } else {
                    Log.e("Notificaciones", "Error en la respuesta de la API: " + responseCode);
                }
            } catch (Exception e) {
                Log.e("Notificaciones", "Excepción al conectar con la API: ", e);
            }

            // 3. Usa la variable 'resultCitas' para actualizar la UI en el hilo principal.
            List<Cita> finalResultCitas = resultCitas;
            mainHandler.post(() -> updateUI(finalResultCitas));

        }).start();
    }

    private List<Cita> parseCitasJson(String jsonResponse) {
        List<Cita> citas = new ArrayList<>();
        try {
            JSONArray jsonArray = new JSONArray(jsonResponse);
            for (int i = 0; i < jsonArray.length(); i++) {
                JSONObject jsonCita = jsonArray.getJSONObject(i);
                citas.add(new Cita(
                        jsonCita.getString("id"),
                        jsonCita.getString("fecha"),
                        jsonCita.getString("tipo_sesion"),
                        jsonCita.getString("estado")
                ));
            }
        } catch (Exception e) {
            Log.e("Notificaciones", "Error al parsear JSON: ", e);
        }
        return citas;
    }

    private void updateUI(List<Cita> citas) {
        adapter = new NotificationAdapter(citas);
        recyclerView.setAdapter(adapter);
    }

    // --- Adaptador para el RecyclerView ---
    private class NotificationAdapter extends RecyclerView.Adapter<NotificationAdapter.NotificationViewHolder> {
        private final List<Cita> citasList;

        public NotificationAdapter(List<Cita> citasList) {
            this.citasList = citasList;
        }

        @NonNull
        @Override
        public NotificationViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
            // Asume que R.layout.item_notificacion existe y tiene los IDs necesarios.
            View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_notificacion, parent, false);
            return new NotificationViewHolder(view);
        }

        @Override
        public void onBindViewHolder(@NonNull NotificationViewHolder holder, int position) {
            Cita cita = citasList.get(position);

            // Formato de fecha para mejor lectura
            String formattedDate = formatFechaHora(cita.fecha);

            // Establecer textos
            holder.title.setText(cita.tipoSesion + " (" + cita.estado.toUpperCase(Locale.ROOT) + ")");
            holder.date.setText(formattedDate);

            // Aplicar estilo de color según el estado
            applyStatusStyle(holder.container, holder.icon, cita.estado);
        }

        @Override
        public int getItemCount() {
            return citasList.size();
        }

        private String formatFechaHora(String fechaHora) {
            try {
                // El formato de entrada es: YYYY-MM-DD HH:MM:SS
                SimpleDateFormat inputFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault());
                inputFormat.setTimeZone(TimeZone.getTimeZone("UTC")); // Asume que la hora del servidor es UTC o estandar
                Date date = inputFormat.parse(fechaHora);

                // Formato de salida deseado: "10 de mayo a las 3:00 PM"
                SimpleDateFormat outputFormat = new SimpleDateFormat("dd 'de' MMMM 'a las' hh:mm a", new Locale("es", "ES"));
                return outputFormat.format(date);
            } catch (Exception e) {
                Log.e("Notificaciones", "Error al formatear fecha: " + fechaHora, e);
                return fechaHora; // Devuelve la cadena original si falla
            }
        }

        private void applyStatusStyle(LinearLayout container, ImageView icon, String estado) {
            int backgroundResId;
            // Se define un color constante para el icono que asegure el contraste.
            int iconTint;

            switch (estado.toLowerCase(Locale.ROOT)) {
                case "confirmado":
                    // Color verde pálido para citas confirmadas
                    backgroundResId = R.drawable.rounded_notification_green;
                    iconTint = 0xFF28A745; // Tinte verde (funciona bien sobre fondo pálido)
                    break;
                case "cancelado":
                    // Color rojo brillante para citas canceladas
                    backgroundResId = R.drawable.rounded_notification_red;
                    // Solución: Usar BLANCO para asegurar el contraste sobre el fondo rojo oscuro
                    iconTint = 0xFFFFFFFF; // Blanco
                    break;
                case "reprogramado":
                    // Color amarillo/naranja brillante para citas reprogramadas
                    backgroundResId = R.drawable.rounded_notification_yellow;
                    // Solución: Usar BLANCO para asegurar el contraste sobre el fondo amarillo/naranja
                    iconTint = 0xFFFFFFFF; // Blanco
                    break;
                case "finalizado":
                    // Color gris para citas finalizadas
                    backgroundResId = R.drawable.rounded_notification_gray;
                    // Usar un color oscuro para contraste sobre el gris pálido
                    iconTint = 0xFF6C757D; // Gris oscuro
                    break;
                default:
                    // Color azul por defecto
                    backgroundResId = R.drawable.rounded_notification_blue;
                    // Usar BLANCO si el azul es oscuro, o un color oscuro si el azul es pálido. Asumiendo azul oscuro:
                    iconTint = 0xFFFFFFFF; // Blanco
                    break;
            }

            container.setBackgroundResource(backgroundResId);
            icon.setColorFilter(iconTint);
        }

        public class NotificationViewHolder extends RecyclerView.ViewHolder {
            public final TextView title;
            public final TextView date;
            public final LinearLayout container;
            public final ImageView icon; // Cambiado de android.widget.ImageView a ImageView

            public NotificationViewHolder(View view) {
                super(view);
                title = view.findViewById(R.id.notification_title);
                date = view.findViewById(R.id.notification_date);
                container = view.findViewById(R.id.notification_container);
                icon = view.findViewById(R.id.notification_icon);
            }
        }
    }


    /**
     * --- Inicialización y Listener de la Barra de Navegación ---
     * Este método inicializa el BottomNavigationView y establece el listener.
     */
    private void setupBottomNavigationInit() {
        // Inicializa la variable de instancia
        bottomNav = findViewById(R.id.bottom_nav_view);

        // Se establece la selección inicial cuando se crea la Activity
        // Asume que R.id.nav_notificaciones está definido en tu menú.
        bottomNav.setSelectedItemId(R.id.nav_notificaciones);

        bottomNav.setOnItemSelectedListener(item -> {
            int itemId = item.getItemId();
            Intent intent = null;

            // Navegación a las Activities principales
            if (itemId == R.id.nav_inicio) {
                // Navegar a MainActivity (Inicio)
                intent = new Intent(this, MainActivity.class);
            } else if (itemId == R.id.nav_citas) {
                // Navegar a CitasActivity
                intent = new Intent(this, CitasActivity.class);
            }
            // Si es R.id.nav_notificaciones, devolvemos 'true' y no hacemos nada más, ya que estamos aquí.

            if (intent != null) {
                // Utilizar FLAG_ACTIVITY_REORDER_TO_FRONT para manejar el historial de la pila
                intent.setFlags(Intent.FLAG_ACTIVITY_REORDER_TO_FRONT);
                startActivity(intent);
                return true;
            }
            // Devolver true para la pestaña actual, o false si el ID no se maneja
            return itemId == R.id.nav_notificaciones;
        });
    }
}
