package com.example.viviendoenresiliencia;
import android.content.Context;
import android.content.Intent; // Importación añadida
import android.content.SharedPreferences;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.CalendarView;
import android.widget.LinearLayout;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.JsonArrayRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
import java.util.Locale;

public class CitasActivity extends AppCompatActivity {

    private static final String KEY_ID_USUARIO = "idUsuario";
    // *** ¡Asegúrate de que esta URL base sea accesible desde el emulador o dispositivo! ***
    private static final String HORARIO_API_BASE_URL = "http://192.168.100.239/proyectofinalMW"; // Usar 10.0.2.2 para localhost en emulador
    private static final String SHARED_PREFS_NAME = "UserSessionPrefs";

    // Claves para Intent (usadas también en ConfirmacionCitaActivity)
    public static final String EXTRA_ID_USUARIO = "com.example.viviendoenresiliencia.ID_USUARIO";
    public static final String EXTRA_FECHA_HORA = "com.example.viviendoenresiliencia.FECHA_HORA";
    public static final String EXTRA_TIPO_SESION = "com.example.viviendoenresiliencia.TIPO_SESION";


    private RequestQueue requestQueue;
    private SharedPreferences sharedPref;

    private Spinner tipoSesionSpinner;
    private CalendarView calendarView;
    private TextView fechaSeleccionadaDisplay;
    private LinearLayout horariosListContainer;
    private Button continuarBtn;

    // Variables de estado
    private String idUsuarioLoggeado;
    private String selectedDate = ""; // YYYY-MM-DD
    private String selectedHora = ""; // HH:MM - HH:MM
    private String selectedTipoSesion = "Individual"; // Valor por defecto

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        try {

            super.onCreate(savedInstanceState);
            setContentView(R.layout.activity_citas); // Asegúrate de tener activity_citas.xml

            // Inicialización de Volley
            requestQueue = Volley.newRequestQueue(this);

            // Inicialización de SharedPreferences y obtención del ID de usuario
            sharedPref = getSharedPreferences(SHARED_PREFS_NAME, Context.MODE_PRIVATE);
            idUsuarioLoggeado = sharedPref.getString(KEY_ID_USUARIO, null);

            if (idUsuarioLoggeado == null) {
                Toast.makeText(this, "Error: Usuario no loggeado.", Toast.LENGTH_LONG).show();
                // Redirigir al login o finalizar
                finish();
                return;
            }

            // Inicialización de vistas
            tipoSesionSpinner = findViewById(R.id.tipo_sesion_spinner);
            calendarView = findViewById(R.id.calendar_view);
            fechaSeleccionadaDisplay = findViewById(R.id.fecha_seleccionada_display);
            horariosListContainer = findViewById(R.id.horarios_list_container);
            continuarBtn = findViewById(R.id.continuar_btn);

            setupTipoSesionSpinner();
            setupCalendarView();
            setupContinuarButton();

            // Cargar horarios del día actual al iniciar
            Calendar cal = Calendar.getInstance();
            int year = cal.get(Calendar.YEAR);
            int month = cal.get(Calendar.MONTH); // 0-11
            int day = cal.get(Calendar.DAY_OF_MONTH);

            // Formatear la fecha actual a YYYY-MM-DD y simular la selección
            selectedDate = String.format(Locale.US, "%d-%02d-%02d", year, month + 1, day);
            seleccionarDia(year, month, day); // month en CalendarView es 0-11
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    // ==============================================================================
    // 1. Configuración de UI
    // ==============================================================================

    private void setupTipoSesionSpinner() {
        ArrayAdapter<CharSequence> adapter = ArrayAdapter.createFromResource(
                this,
                R.array.tipos_sesion_array, // Asegúrate de definir este array en strings.xml
                android.R.layout.simple_spinner_item
        );
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        tipoSesionSpinner.setAdapter(adapter);

        tipoSesionSpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                selectedTipoSesion = parent.getItemAtPosition(position).toString();
                // Deshabilitar el botón hasta que se seleccione una hora
                continuarBtn.setEnabled(false);
            }
            @Override
            public void onNothingSelected(AdapterView<?> parent) {}
        });
    }

    private void setupCalendarView() {
        // *** RESTRICCIÓN DE FECHAS PASADAS: Establecer la fecha mínima seleccionable a hoy ***
        calendarView.setMinDate(Calendar.getInstance().getTimeInMillis());
        // *** RESTRICCIÓN DE DÍAS NO LABORABLES ELIMINADA (por petición del usuario) ***

        calendarView.setOnDateChangeListener((view, year, month, dayOfMonth) -> {

            // El mes es de 0 a 11, lo ajustamos para la URL y formato (1 a 12)
            selectedDate = String.format(Locale.US, "%d-%02d-%02d", year, month + 1, dayOfMonth);
            seleccionarDia(year, month, dayOfMonth);
        });
    }

    // ==============================================================================
    // 2. Lógica de Horarios (Volley)
    // ==============================================================================

    private void seleccionarDia(int year, int month, int dayOfMonth) {
        // 1. Deshabilitar el botón y limpiar la hora seleccionada
        selectedHora = "";
        continuarBtn.setEnabled(false);

        // 2. Mostrar indicador de carga
        horariosListContainer.removeAllViews();
        TextView loading = new TextView(this);
        loading.setText("Cargando horarios y verificando disponibilidad...");
        loading.setPadding(0, 16, 0, 16);
        loading.setGravity(View.TEXT_ALIGNMENT_CENTER);
        horariosListContainer.addView(loading);

        // 3. Formatear la fecha para el display (ej: lunes 14 de octubre)
        Calendar cal = Calendar.getInstance();
        cal.set(year, month, dayOfMonth);
        // Usar español para formatear el día y mes
        SimpleDateFormat fullFormat = new SimpleDateFormat("EEEE d 'de' MMMM", new Locale("es", "ES"));
        fechaSeleccionadaDisplay.setText(fullFormat.format(cal.getTime()));

        // 4. Obtener el día de la semana (1=Lunes, 7=Domingo) para la API
        int diaSemanaNum = cal.get(Calendar.DAY_OF_WEEK);
        // Ajustar para que Lunes sea 1 (PHP/MySQL style: 1=Lunes, ..., 7=Domingo)
        diaSemanaNum = (diaSemanaNum == Calendar.SUNDAY) ? 7 : diaSemanaNum - 1;

        // 5. Hacer el Fetch a la API de horarios (horario general) usando Volley
        String urlHorario = HORARIO_API_BASE_URL + "/horarios/dia?dia=" + diaSemanaNum;

        JsonArrayRequest jsonArrayRequest = new JsonArrayRequest(
                Request.Method.GET,
                urlHorario,
                null,
                new Response.Listener<JSONArray>() {
                    @Override
                    public void onResponse(JSONArray response) {
                        // Después de obtener el horario general, obtenemos las citas agendadas
                        List<String> horariosBase = parseHorarios(response);
                        fetchCitasAndFilter(horariosBase);
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        String message = "Error de conexión o servidor al obtener horarios. ";
                        if (error.networkResponse != null) {
                            message += "Código: " + error.networkResponse.statusCode;
                        } else {
                            message += error.getMessage();
                        }
                        mostrarMensajeHorario(message);
                        Log.e("VolleyError", message, error);
                    }
                }
        );

        requestQueue.add(jsonArrayRequest);
    }

    /**
     * Llama al API de citas para obtener las citas agendadas y luego filtra
     * los horarios base para mostrar solo los disponibles.
     * @param horariosBase Lista de slots de tiempo general (e.g., ["09:00 - 10:00", ...]).
     */
    private void fetchCitasAndFilter(List<String> horariosBase) {
        if (horariosBase.isEmpty()) {
            mostrarMensajeHorario("No hay horarios de atención configurados para este día.");
            return;
        }

        // URL para obtener todas las citas
        final String urlCitas = HORARIO_API_BASE_URL + "/citas";

        JsonArrayRequest citasRequest = new JsonArrayRequest(
                Request.Method.GET,
                urlCitas,
                null,
                new Response.Listener<JSONArray>() {
                    @Override
                    public void onResponse(JSONArray response) {
                        // Filtrar y mostrar los horarios disponibles
                        List<String> horariosFiltrados = filtrarHorarios(horariosBase, response, selectedDate);
                        mostrarHorarios(horariosFiltrados);
                    }
                },
                new Response.ErrorListener() {
                    @Override
                    public void onErrorResponse(VolleyError error) {
                        // Si falla la API de citas, asumimos que no hay citas agendadas confirmadas para este día
                        Log.e("VolleyError", "Error al obtener citas. Mostrando horarios base sin filtrar.", error);
                        Toast.makeText(CitasActivity.this, "Advertencia: No se pudo verificar la disponibilidad. Mostrando horarios base.", Toast.LENGTH_LONG).show();
                        mostrarHorarios(horariosBase); // Mostrar horarios base si falla la API de citas
                    }
                }
        );
        requestQueue.add(citasRequest);
    }

    /**
     * Filtra la lista de horarios base, eliminando aquellos que ya tienen una cita confirmada
     * para la fecha seleccionada.
     * @param horariosBase Lista de slots de tiempo base (e.g., "09:00 - 10:00").
     * @param citasJsonArray Respuesta JSON de la API de citas.
     * @param fechaSeleccionada Fecha seleccionada en formato YYYY-MM-DD.
     * @return Lista de slots de tiempo disponibles.
     */
    private List<String> filtrarHorarios(List<String> horariosBase, JSONArray citasJsonArray, String fechaSeleccionada) {
        List<String> horariosOcupados = new ArrayList<>();

        // 1. Identificar todas las horas ocupadas (confirmadas) para la fecha seleccionada.
        for (int i = 0; i < citasJsonArray.length(); i++) {
            try {
                JSONObject cita = citasJsonArray.getJSONObject(i);
                String fechaCompleta = cita.getString("fecha"); // e.g., "2025-10-08 17:20:00"
                String estado = cita.getString("estado"); // e.g., "confirmado"

                if ("confirmado".equalsIgnoreCase(estado) && fechaCompleta.startsWith(fechaSeleccionada)) {
                    // Extraer solo la hora de inicio (HH:MM) del slot que ocupa la cita
                    String horaInicioConMin = fechaCompleta.substring(11, 16);

                    // Normalizar la hora de inicio a XX:00 (inicio del slot de 1 hora)
                    String horaBase = horaInicioConMin.substring(0, 2) + ":00";

                    // Calcular la hora de fin del slot (una hora después)
                    Calendar tempCal = Calendar.getInstance();
                    tempCal.set(Calendar.HOUR_OF_DAY, Integer.parseInt(horaBase.substring(0, 2)));
                    tempCal.set(Calendar.MINUTE, 0);
                    tempCal.add(Calendar.HOUR_OF_DAY, 1);
                    String horaFin = String.format(Locale.US, "%02d:%02d", tempCal.get(Calendar.HOUR_OF_DAY), tempCal.get(Calendar.MINUTE));

                    String slotOcupado = horaBase + " - " + horaFin;

                    if (!horariosOcupados.contains(slotOcupado)) {
                        horariosOcupados.add(slotOcupado);
                    }
                }
            } catch (JSONException e) {
                Log.e("JSONFilter", "Error al procesar objeto de cita: " + e.getMessage());
            } catch (Exception e) {
                Log.e("DateParse", "Error al parsear hora de cita: " + e.getMessage());
            }
        }

        // 2. Filtrar los horarios base
        List<String> horariosDisponibles = new ArrayList<>();
        for (String slot : horariosBase) {
            if (!horariosOcupados.contains(slot)) {
                horariosDisponibles.add(slot);
            }
        }

        return horariosDisponibles;
    }


    private List<String> parseHorarios(JSONArray response) {
        List<String> slots = new ArrayList<>();
        try {
            if (response.length() > 0) {
                JSONObject h = response.getJSONObject(0);
                boolean esLaborable = h.optInt("es_laborable",0)==1;

                if (esLaborable) {
                    String inicio = h.getString("hora_inicio").substring(0, 5);
                    String fin = h.getString("hora_fin").substring(0, 5);
                    String inicioDescanso = h.optString("inicio_descanso", "").substring(0, 5);
                    String finDescanso = h.optString("fin_descanso", "").substring(0, 5);

                    // Lógica de generación de slots de 1 hora (idéntica a la PHP/JS)
                    Calendar current = Calendar.getInstance();
                    current.set(Calendar.HOUR_OF_DAY, Integer.parseInt(inicio.substring(0, 2)));
                    current.set(Calendar.MINUTE, Integer.parseInt(inicio.substring(3, 5)));
                    current.set(Calendar.SECOND, 0);

                    Calendar end = Calendar.getInstance();
                    end.set(Calendar.HOUR_OF_DAY, Integer.parseInt(fin.substring(0, 2)));
                    end.set(Calendar.MINUTE, Integer.parseInt(fin.substring(3, 5)));
                    current.set(Calendar.SECOND, 0);

                    while (current.before(end)) {
                        Calendar next = (Calendar) current.clone();
                        next.add(Calendar.HOUR_OF_DAY, 1);

                        if (next.after(end)) break;

                        String currentStr = String.format(Locale.US, "%02d:%02d", current.get(Calendar.HOUR_OF_DAY), current.get(Calendar.MINUTE));
                        String nextStr = String.format(Locale.US, "%02d:%02d", next.get(Calendar.HOUR_OF_DAY), next.get(Calendar.MINUTE));

                        boolean enDescanso = false;
                        if (!inicioDescanso.isEmpty() && !finDescanso.isEmpty()) {
                            if (currentStr.compareTo(finDescanso) < 0 && nextStr.compareTo(inicioDescanso) > 0) {
                                enDescanso = true;
                            }
                        }

                        if (!enDescanso) {
                            slots.add(currentStr + " - " + nextStr);
                        }
                        current = next;
                    }
                }
            }
        } catch (JSONException e) {
            Log.e("JSONError", "Error al parsear horarios: " + e.getMessage());
            Toast.makeText(this, "Error al procesar datos del servidor.", Toast.LENGTH_SHORT).show();
        }
        return slots;
    }

    // ==============================================================================
    // 3. UI y Botón de Continuar (Modificada para Intent)
    // ==============================================================================

    private void mostrarHorarios(List<String> horarios) {
        horariosListContainer.removeAllViews();

        if (horarios.isEmpty()) {
            mostrarMensajeHorario("No hay horarios disponibles para esta fecha. Intenta otro día.");
            return;
        }

        for (String horaCompleta : horarios) {
            String hora = horaCompleta.split(" - ")[0];

            LinearLayout.LayoutParams layoutParams = new LinearLayout.LayoutParams(
                    LinearLayout.LayoutParams.MATCH_PARENT, LinearLayout.LayoutParams.WRAP_CONTENT);

            LinearLayout hourLayout = new LinearLayout(this);
            hourLayout.setLayoutParams(layoutParams);
            hourLayout.setOrientation(LinearLayout.HORIZONTAL);
            hourLayout.setPadding(0, 8, 0, 8);

            TextView horaTxt = new TextView(this);
            horaTxt.setText(horaCompleta);
            horaTxt.setTextSize(16);
            // Usamos colores predeterminados o definidos en res/values/colors.xml
            horaTxt.setTextColor(getResources().getColor(android.R.color.black));
            horaTxt.setLayoutParams(new LinearLayout.LayoutParams(0, LinearLayout.LayoutParams.WRAP_CONTENT, 1.0f));
            hourLayout.addView(horaTxt);

            Button selectBtn = new Button(this);
            selectBtn.setText("Seleccionar");
            selectBtn.setTag(horaCompleta);
            // Nota: R.color.black no existe por defecto, pero se usa para consistencia con tu código original
            selectBtn.setBackgroundColor(getResources().getColor(android.R.color.transparent));
            selectBtn.setTextColor(getResources().getColor(android.R.color.black));
            selectBtn.setPadding(16, 8, 16, 8);

            selectBtn.setOnClickListener(v -> seleccionarHora(horaCompleta, selectBtn));

            hourLayout.addView(selectBtn);
            horariosListContainer.addView(hourLayout);

            View divider = new View(this);
            divider.setLayoutParams(new LinearLayout.LayoutParams(LinearLayout.LayoutParams.MATCH_PARENT, 1));
            divider.setBackgroundColor(getResources().getColor(android.R.color.darker_gray));
            horariosListContainer.addView(divider);
        }
    }

    private void mostrarMensajeHorario(String mensaje) {
        horariosListContainer.removeAllViews();
        TextView message = new TextView(this);
        message.setText(mensaje);
        message.setPadding(0, 16, 0, 16);
        message.setGravity(View.TEXT_ALIGNMENT_CENTER);
        horariosListContainer.addView(message);
    }

    private void seleccionarHora(String horaCompleta, Button selectedButton) {
        // Limpiar selección previa
        for (int i = 0; i < horariosListContainer.getChildCount(); i += 2) {
            View view = horariosListContainer.getChildAt(i);
            if (view instanceof LinearLayout) {
                Button btn = (Button) ((LinearLayout) view).getChildAt(1);
                btn.setText("Seleccionar");
                btn.setTextColor(getResources().getColor(android.R.color.black));
                btn.setBackgroundColor(getResources().getColor(android.R.color.transparent));
            }
        }

        // Aplicar estilos a la hora actual
        selectedButton.setText("Seleccionado");
        selectedButton.setTextColor(getResources().getColor(android.R.color.white));
        // Nota: Asumo que R.color.violeta está definido
        selectedButton.setBackgroundColor(getResources().getColor(R.color.violeta));

        selectedHora = horaCompleta;
        continuarBtn.setEnabled(true);
    }

    private void setupContinuarButton() {
        continuarBtn.setText("Continuar"); // Cambiamos el texto del botón
        continuarBtn.setOnClickListener(v -> {
            if (selectedHora.isEmpty()) {
                Toast.makeText(CitasActivity.this, "Por favor, selecciona una hora.", Toast.LENGTH_SHORT).show();
                return;
            }

            // Preparar los datos para el envío a la Activity de Confirmación
            String horaInicio = selectedHora.split(" - ")[0];
            // Formato final requerido por el API: YYYY-MM-DD HH:MM:00
            final String fechaHoraAPI = selectedDate + " " + horaInicio + ":00";

            // 1. Crear el Intent para la nueva Activity
            Intent intent = new Intent(CitasActivity.this, ConfirmacionCitaActivity.class);

            // 2. Adjuntar los datos como Extras
            intent.putExtra(EXTRA_ID_USUARIO, idUsuarioLoggeado);
            intent.putExtra(EXTRA_FECHA_HORA, fechaHoraAPI);
            intent.putExtra(EXTRA_TIPO_SESION, selectedTipoSesion);

            // 3. Iniciar la Activity
            startActivity(intent);
        });
    }
}
