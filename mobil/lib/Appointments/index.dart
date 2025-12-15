import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';
import '../global.dart';

class AppointmentsPage extends StatefulWidget {
  const AppointmentsPage({super.key});

  @override
  State<AppointmentsPage> createState() => _AppointmentsPageState();
}

class _AppointmentsPageState extends State<AppointmentsPage> {
  List customers = [];
  List users = [];
  List appointments = [];
  List services = [];

  bool loading = true;

  // Form alanları
  String? selectedCustomer;
  String? selectedUser;
  String? selectedService;
  String? selectedDate = DateFormat("yyyy-MM-dd").format(DateTime.now());

  TextEditingController noteController = TextEditingController();
  TextEditingController costController = TextEditingController();
  TextEditingController serviceSearchController = TextEditingController();
  TextEditingController customerSearchController = TextEditingController();

  List<Map<String, dynamic>> timeSlots = [];
  List<int> selectedSlots = [];

  @override
  void initState() {
    super.initState();
    fetchData();
    generateTimeSlots();
  }

  Future<void> fetchData() async {
    setState(() => loading = true);

    final url = Uri.parse("$apiBaseUrl/api/appointments");
    final serviceUrl = Uri.parse("$apiBaseUrl/api/service");

    try {
      final res = await http.get(
        url,
        headers: {"Authorization": "Bearer $globalToken"},
      );

      final serviceRes = await http.get(
        serviceUrl,
        headers: {"Authorization": "Bearer $globalToken"},
      );

      if (res.statusCode == 200 && serviceRes.statusCode == 200) {
        final data = jsonDecode(res.body);
        final serviceData = jsonDecode(serviceRes.body);

        setState(() {
          customers = data["customers"];
          users = data["users"];
          appointments = data["appointments"];
          services = serviceData;
          loading = false;
        });
      } else {
        setState(() => loading = false);
      }
    } catch (e) {
      setState(() => loading = false);
    }
  }

  /// 08:00 - 20:00 arası 30 dk slot oluşturma
  void generateTimeSlots() {
    timeSlots.clear();

    int hour = 8;
    int min = 0;

    while (hour < 20) {
      String t =
          "${hour.toString().padLeft(2, '0')}:${min.toString().padLeft(2, '0')}";
      timeSlots.add({"time": t, "busy": false});

      min += 30;
      if (min >= 60) {
        min = 0;
        hour++;
      }
    }
  }

  /// Busy slot hesaplama
  void updateBusySlots() {
    if (selectedUser == null || selectedDate == null) return;

    for (var slot in timeSlots) {
      slot["busy"] = appointments.any((a) {
        if (a["user_id"].toString() != selectedUser) return false;

        DateTime slotStart = DateTime.parse(
          "${selectedDate!} ${slot['time']}:00",
        );
        DateTime slotEnd = slotStart.add(const Duration(minutes: 29));

        DateTime aStart = DateTime.parse(a["start_datetime"]);
        DateTime aEnd = DateTime.parse(a["end_datetime"]);

        return aStart.isBefore(slotEnd) && aEnd.isAfter(slotStart);
      });
    }

    setState(() {});
  }

  /// Slot seçme
  void toggleSlot(int index) {
    var slot = timeSlots[index];
    if (slot["busy"] == true) return;

    if (selectedSlots.contains(index)) {
      selectedSlots.remove(index);
    } else {
      // Ardışık saat kontrolü
      if (selectedSlots.isNotEmpty) {
        selectedSlots.sort();
        int min = selectedSlots.first;
        int max = selectedSlots.last;

        if (!(index == min - 1 || index == max + 1)) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text("Sadece ardışık saatler seçebilirsiniz!"),
            ),
          );
          return;
        }
      }
      selectedSlots.add(index);
    }

    setState(() {});
  }

  /// Randevu kaydetme
  Future<void> saveAppointment() async {
    if (selectedSlots.isEmpty) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text("Saat seçmelisiniz!")));
      return;
    }

    selectedSlots.sort();
    String startTime = timeSlots[selectedSlots.first]["time"];
    String endTime = timeSlots[selectedSlots.last]["time"];

    DateTime end = DateTime.parse(
      "$selectedDate $endTime:00",
    ).add(const Duration(minutes: 29));

    // Tip dönüşümleri: id'leri int, cost'u number olarak gönder
    final int? customerIdInt = selectedCustomer != null
        ? int.tryParse(selectedCustomer!)
        : null;
    final int? userIdInt = selectedUser != null
        ? int.tryParse(selectedUser!)
        : null;
    final costValue =
        double.tryParse(costController.text.replaceAll(',', '.')) ?? 0;

    final payload = {
      "customer_id": customerIdInt,
      "employee_id": userIdInt,
      // backend model'inde date yok, ama bırakmak istersen sorun olmaz
      "start_datetime": "$selectedDate $startTime:00",
      "end_datetime": end.toString().substring(0, 19),
      "service": selectedService ?? "",
      "cost": costValue,
      "note": noteController.text,
    };

    final url = Uri.parse("$apiBaseUrl/api/appointments");

    // DEBUG: request log
    print("📡 API URL: $url");
    print("🔑 Token: $globalToken");
    print("📤 Gönderilen Payload (JSON): ${jsonEncode(payload)}");

    try {
      final res = await http.post(
        url,
        headers: {
          "Authorization": "Bearer $globalToken",
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: jsonEncode(payload),
      );

      print("📥 API Response Status: ${res.statusCode}");
      print("📥 API Response Body: ${res.body}");

      // Yanıtı parse et ve kullanıcıya ayrıntı göster
      if (res.statusCode == 200 || res.statusCode == 201) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text("Randevu kaydedildi!"),
            backgroundColor: Colors.green,
          ),
        );
        selectedSlots.clear();
        await fetchData();
        setState(() {});
        return;
      }

      // Validation hatalarını (422) veya json mesajını göster
      String message = "Randevu kaydedilemedi (status: ${res.statusCode})";
      try {
        final body = jsonDecode(res.body);
        // Laravel genelde { "message": "...", "errors": { ... } }
        if (body is Map) {
          if (body['message'] != null) message = body['message'].toString();
          if (body['errors'] != null) {
            final errors = body['errors'] as Map;
            final firstErrorField = errors.keys.isNotEmpty
                ? errors.keys.first
                : null;
            if (firstErrorField != null) {
              final errList = errors[firstErrorField];
              if (errList is List && errList.isNotEmpty) {
                message = "${firstErrorField}: ${errList[0]}";
              }
            }
          }
        }
      } catch (_) {
        // body JSON değilse ignore
      }

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(message), backgroundColor: Colors.red),
      );
    } catch (e) {
      print("🔥 POST hata: $e");
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text("Ağ hatası: $e"), backgroundColor: Colors.red),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    "Randevu Oluştur",
                    style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
                  ),

                  const SizedBox(height: 20),

                  // Hizmet Arama
                  TextField(
                    controller: serviceSearchController,
                    decoration: const InputDecoration(
                      labelText: "Hizmet Ara",
                      border: OutlineInputBorder(),
                    ),
                    onChanged: (_) => setState(() {}),
                  ),
                  const SizedBox(height: 8),

                  // Hizmet listesi
                  ...services
                      .where(
                        (s) => s["name"].toLowerCase().contains(
                          serviceSearchController.text.toLowerCase(),
                        ),
                      )
                      .take(5)
                      .map(
                        (s) => ListTile(
                          title: Text("${s["name"]} (${s["cost"]}₺)"),
                          onTap: () {
                            selectedService = s["name"];
                            costController.text = s["cost"].toString();
                            serviceSearchController.text = s["name"];
                            setState(() {});
                          },
                        ),
                      ),

                  const SizedBox(height: 20),

                  // Ücret
                  TextField(
                    controller: costController,
                    decoration: const InputDecoration(
                      labelText: "Ücret",
                      border: OutlineInputBorder(),
                    ),
                  ),

                  const SizedBox(height: 20),

                  // Müşteri Arama
                  TextField(
                    controller: customerSearchController,
                    decoration: const InputDecoration(
                      labelText: "Müşteri Ara",
                      border: OutlineInputBorder(),
                    ),
                    onChanged: (_) => setState(() {}),
                  ),
                  const SizedBox(height: 8),

                  // Müşteri Listesi (filtreli)
                  ...customers
                      .where((c) {
                        final fullName = "${c["first_name"]} ${c["last_name"]}"
                            .toLowerCase();
                        return fullName.contains(
                          customerSearchController.text.toLowerCase(),
                        );
                      })
                      .take(5)
                      .map(
                        (c) => ListTile(
                          title: Text("${c["first_name"]} ${c["last_name"]}"),
                          subtitle: Text(c["phone"] ?? ""),
                          onTap: () {
                            selectedCustomer = c["id"].toString();
                            customerSearchController.text =
                                "${c["first_name"]} ${c["last_name"]}";
                            setState(() {});
                          },
                        ),
                      ),

                  const SizedBox(height: 20),

                  // Çalışan seçimi
                  DropdownButtonFormField(
                    decoration: const InputDecoration(
                      labelText: "Çalışan",
                      border: OutlineInputBorder(),
                    ),
                    value: selectedUser,
                    items: users
                        .map(
                          (u) => DropdownMenuItem(
                            value: u["id"].toString(),
                            child: Text("${u['first_name']} ${u['last_name']}"),
                          ),
                        )
                        .toList(),
                    onChanged: (v) {
                      selectedUser = v;
                      updateBusySlots();
                    },
                  ),

                  const SizedBox(height: 20),

                  // Tarih seçimi
                  TextField(
                    decoration: const InputDecoration(
                      labelText: "Tarih",
                      border: OutlineInputBorder(),
                    ),
                    readOnly: true,
                    controller: TextEditingController(text: selectedDate),
                    onTap: () async {
                      DateTime? d = await showDatePicker(
                        context: context,
                        initialDate: DateTime.now(),
                        firstDate: DateTime.now(),
                        lastDate: DateTime(2030),
                      );
                      if (d != null) {
                        selectedDate = DateFormat(
                          "yyyy-MM-dd",
                        ).format(d).toString();
                        updateBusySlots();
                        setState(() {});
                      }
                    },
                  ),

                  const SizedBox(height: 20),

                  const Text(
                    "Saat Seçimi",
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 10),

                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: List.generate(timeSlots.length, (i) {
                      bool busy = timeSlots[i]["busy"];
                      bool selected = selectedSlots.contains(i);

                      return GestureDetector(
                        onTap: () => toggleSlot(i),
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            vertical: 8,
                            horizontal: 12,
                          ),
                          decoration: BoxDecoration(
                            color: busy
                                ? Colors.red.shade200
                                : selected
                                ? Colors.blue.shade300
                                : Colors.green.shade200,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(timeSlots[i]["time"]),
                        ),
                      );
                    }),
                  ),

                  const SizedBox(height: 20),

                  // Not
                  TextField(
                    controller: noteController,
                    decoration: const InputDecoration(
                      labelText: "Not",
                      border: OutlineInputBorder(),
                    ),
                    maxLines: 3,
                  ),

                  const SizedBox(height: 20),

                  // Kaydet
                  ElevatedButton(
                    onPressed: saveAppointment,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.blue,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    child: const Center(
                      child: Text(
                        "Kaydet",
                        style: TextStyle(color: Colors.white, fontSize: 16),
                      ),
                    ),
                  ),
                ],
              ),
            ),
    );
  }
}
