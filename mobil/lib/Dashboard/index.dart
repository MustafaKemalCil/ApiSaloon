import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:http/http.dart' as http;
import '../global.dart';
import 'package:mobil/widgets/widgets.dart';

class DashboardPage extends StatefulWidget {
  const DashboardPage({super.key});

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  bool loading = true;
  List workers = [];
  List appointments = [];
  DateTime selectedDate = DateTime.now();
  int visibleStartIndex = 0;
  int visibleCount = 2;
  TextEditingController amountController = TextEditingController();
  TextEditingController noteController = TextEditingController();
  @override
  void initState() {
    super.initState();
    fetchDashboardData();
  }

  Future<void> fetchDashboardData() async {
    try {
      final uri = Uri.parse("$apiBaseUrl/api/dashboard");
      final response = await http.get(
        uri,
        headers: {
          "Accept": "application/json",
          "Authorization": "Bearer $globalToken",
        },
      );

      final data = jsonDecode(response.body);

      setState(() {
        workers = data["users"] ?? [];
        appointments = data["appointments"] ?? [];
        loading = false;
      });
    } catch (e) {
      print("Dashboard Error: $e");
    }
  }

  Future<Map<String, dynamic>> fetchPayments(int appointmentId) async {
    final url = Uri.parse(
      "$apiBaseUrl/api/appointments/$appointmentId/payments",
    );

    final response = await http.get(
      url,
      headers: {
        "Accept": "application/json",
        "Authorization": "Bearer $globalToken",
      },
    );

    return jsonDecode(response.body);
  }

  // Randevuyu tüm slotlara yaymak için slotMap oluşturuyoruz
  Map<String, Map<int, dynamic>> buildSlotMap(List appointments) {
    Map<String, Map<int, dynamic>> map = {};

    for (var w in workers) {
      map[w['id'].toString()] = {};
    }

    for (var appt in appointments) {
      DateTime start = DateTime.parse(appt["start_datetime"]);
      DateTime end = DateTime.parse(appt["end_datetime"]);

      // Sadece selectedDate içindeki randevuları ekle
      if (start.year != selectedDate.year ||
          start.month != selectedDate.month ||
          start.day != selectedDate.day)
        continue;

      int startSlot = start.hour * 2 + (start.minute >= 30 ? 1 : 0);

      // endSlot'u 30 dakikalık blok mantığına göre ayarla
      int endSlot = end.hour * 2 + ((end.minute + 29) ~/ 30);

      // Eğer bitiş dakikası 30'un katı değilse, bir sonraki slotu da dahil et
      if (end.minute % 30 != 0) endSlot++;

      for (int slot = startSlot; slot < endSlot; slot++) {
        map[appt["user_id"].toString()]![slot] = appt;
      }
    }

    return map;
  }

  List get dailyAppointments {
    return appointments.where((appt) {
      final dt = DateTime.parse(appt["start_datetime"]);
      return dt.year == selectedDate.year &&
          dt.month == selectedDate.month &&
          dt.day == selectedDate.day;
    }).toList();
  }

  List<DateTime> generateTimeSlots() {
    List<DateTime> slots = [];
    DateTime start = DateTime(
      selectedDate.year,
      selectedDate.month,
      selectedDate.day,
      8,
      0,
    );
    DateTime end = DateTime(
      selectedDate.year,
      selectedDate.month,
      selectedDate.day,
      20,
      0,
    );

    while (start.isBefore(end)) {
      slots.add(start);
      start = start.add(const Duration(minutes: 30));
    }
    return slots;
  }

  @override
  Widget build(BuildContext context) {
    if (loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    final timeSlots = generateTimeSlots();
    final slotMap = buildSlotMap(dailyAppointments);

    return Scaffold(
      body: Column(
        children: [
          /// ✅ Tarih seçimi (kart görünümü)
          Container(
            margin: const EdgeInsets.symmetric(horizontal: 12),
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Center(
              child: ElevatedButton.icon(
                icon: const Icon(Icons.calendar_today, size: 18),
                label: Text(DateFormat("dd MMMM yyyy").format(selectedDate)),
                onPressed: () async {
                  DateTime? picked = await showDatePicker(
                    context: context,
                    firstDate: DateTime(2020),
                    lastDate: DateTime(2030),
                    initialDate: selectedDate,
                  );
                  if (picked != null) {
                    setState(() => selectedDate = picked);
                  }
                },
              ),
            ),
          ),

          const SizedBox(height: 10),

          // Üstte çalışan isimleri
          /// ✅ Çalışanlar barı
          Container(
            margin: const EdgeInsets.symmetric(horizontal: 8),
            height: 60,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Row(
              children: [
                IconButton(
                  icon: const Icon(Icons.chevron_left),
                  onPressed: () {
                    setState(() {
                      if (visibleStartIndex > 0) visibleStartIndex--;
                    });
                  },
                ),
                Expanded(
                  child: SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: workers
                          .skip(visibleStartIndex)
                          .take(visibleCount)
                          .map((w) {
                            return Container(
                              width: 160,
                              margin: const EdgeInsets.symmetric(horizontal: 6),
                              alignment: Alignment.center,
                              decoration: BoxDecoration(
                                color: Colors.grey.shade50,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: Colors.grey.shade300),
                              ),
                              child: Text(
                                "${w['first_name']} ${w['last_name']}",
                                style: const TextStyle(
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            );
                          })
                          .toList(),
                    ),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.chevron_right),
                  onPressed: () {
                    setState(() {
                      if (visibleStartIndex + visibleCount < workers.length)
                        visibleStartIndex++;
                    });
                  },
                ),
              ],
            ),
          ),

          // Tablo (scroll)
          Expanded(
            child: SingleChildScrollView(
              scrollDirection: Axis.vertical,
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Column(
                  children: timeSlots.map((slotStart) {
                    return Row(
                      children: [
                        // SOLDA SAAT KUTUSU
                        Container(
                          width: 50,
                          height: 40,
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            border: Border.all(color: Colors.grey.shade300),
                          ),
                          child: Text(DateFormat("HH:mm").format(slotStart)),
                        ),

                        // ÇALIŞANLARIN SÜTUNLARI
                        ...workers.skip(visibleStartIndex).take(visibleCount).map((
                          user,
                        ) {
                          int slotIndex =
                              slotStart.hour * 2 + (slotStart.minute ~/ 30);

                          final appt =
                              slotMap[user['id'].toString()]![slotIndex];

                          return InkWell(
                            onTap: appt != null
                                ? () {
                                    showDialog(
                                      context: context,
                                      builder: (context) {
                                        String? selectedStatus = appt['status'];

                                        return AlertDialog(
                                          title: const Text("Randevu Detayı"),
                                          content: FutureBuilder(
                                            future: fetchPayments(appt['id']),
                                            builder: (context, snapshot) {
                                              if (!snapshot.hasData) {
                                                return const SizedBox(
                                                  height: 120,
                                                  child: Center(
                                                    child:
                                                        CircularProgressIndicator(),
                                                  ),
                                                );
                                              }

                                              final data =
                                                  snapshot.data
                                                      as Map<String, dynamic>;
                                              final List payments =
                                                  data['payments'] ?? [];

                                              return SizedBox(
                                                width: double.maxFinite,
                                                child: SingleChildScrollView(
                                                  child: Column(
                                                    mainAxisSize:
                                                        MainAxisSize.min,
                                                    crossAxisAlignment:
                                                        CrossAxisAlignment
                                                            .start,
                                                    children: [
                                                      Text(
                                                        "Müşteri: ${appt['customer']['first_name']} ${appt['customer']['last_name']}",
                                                      ),
                                                      const SizedBox(height: 6),

                                                      // Status Dropdown
                                                      StatefulBuilder(
                                                        builder: (context, setState) {
                                                          return Row(
                                                            children: [
                                                              const Text(
                                                                "Durum: ",
                                                              ),
                                                              DropdownButton<
                                                                String
                                                              >(
                                                                value:
                                                                    selectedStatus,
                                                                items:
                                                                    [
                                                                          'pending',
                                                                          'completed',
                                                                          'canceled',
                                                                          'paid',
                                                                        ]
                                                                        .map(
                                                                          (
                                                                            s,
                                                                          ) => DropdownMenuItem(
                                                                            value:
                                                                                s,
                                                                            child: Text(
                                                                              s,
                                                                            ),
                                                                          ),
                                                                        )
                                                                        .toList(),
                                                                onChanged: (value) {
                                                                  setState(() {
                                                                    selectedStatus =
                                                                        value;
                                                                  });
                                                                },
                                                              ),
                                                            ],
                                                          );
                                                        },
                                                      ),

                                                      Text(
                                                        "Hizmet Bedeli: ${appt['cost'] ?? 'Bilinmiyor'} TL",
                                                      ),
                                                      Text(
                                                        "Toplam Ödeme: ${data['total_paid'] ?? 'Bilinmiyor'} TL",
                                                      ),
                                                      Text(
                                                        "Kalan Borç: ${data['remaining'] ?? 'Bilinmiyor'} TL",
                                                      ),
                                                      const SizedBox(
                                                        height: 10,
                                                      ),
                                                      const Text(
                                                        "Yeni Ödeme Ekle",
                                                        style: TextStyle(
                                                          fontWeight:
                                                              FontWeight.bold,
                                                        ),
                                                      ),
                                                      TextField(
                                                        controller:
                                                            amountController,
                                                        keyboardType:
                                                            TextInputType
                                                                .number,
                                                        decoration:
                                                            const InputDecoration(
                                                              labelText:
                                                                  "Tutar",
                                                            ),
                                                      ),
                                                      TextField(
                                                        controller:
                                                            noteController,
                                                        decoration:
                                                            const InputDecoration(
                                                              labelText:
                                                                  "Not (opsiyonel)",
                                                            ),
                                                      ),
                                                      ElevatedButton(
                                                        onPressed: () async {
                                                          if (amountController
                                                              .text
                                                              .isEmpty) {
                                                            ScaffoldMessenger.of(
                                                              context,
                                                            ).showSnackBar(
                                                              const SnackBar(
                                                                content: Text(
                                                                  "Lütfen bir tutar girin.",
                                                                ),
                                                              ),
                                                            );
                                                            return;
                                                          }

                                                          final url = Uri.parse(
                                                            "$apiBaseUrl/api/appointments/${appt['id']}/payments",
                                                          );

                                                          final response = await http.post(
                                                            url,
                                                            headers: {
                                                              "Accept":
                                                                  "application/json",
                                                              "Authorization":
                                                                  "Bearer $globalToken",
                                                              "Content-Type":
                                                                  "application/json",
                                                            },
                                                            body: jsonEncode({
                                                              "amount":
                                                                  double.tryParse(
                                                                    amountController
                                                                        .text,
                                                                  ),
                                                              "note":
                                                                  noteController
                                                                      .text,
                                                              "customer_id":
                                                                  appt['customer']['id'],
                                                            }),
                                                          );

                                                          if (response
                                                                  .statusCode ==
                                                              200) {
                                                            // Başarılıysa dialogu kapat ve listeyi güncelle
                                                            Navigator.pop(
                                                              context,
                                                            );
                                                            fetchDashboardData();
                                                            ScaffoldMessenger.of(
                                                              context,
                                                            ).showSnackBar(
                                                              const SnackBar(
                                                                content: Text(
                                                                  "Ödeme başarıyla eklendi.",
                                                                ),
                                                              ),
                                                            );
                                                          } else {
                                                            ScaffoldMessenger.of(
                                                              context,
                                                            ).showSnackBar(
                                                              const SnackBar(
                                                                content: Text(
                                                                  "Ödeme eklenemedi.",
                                                                ),
                                                              ),
                                                            );
                                                          }
                                                        },
                                                        child: const Text(
                                                          "Ödemeyi Kaydet",
                                                        ),
                                                      ),

                                                      const SizedBox(
                                                        height: 10,
                                                      ),
                                                      const Text(
                                                        "Ödemeler:",
                                                        style: TextStyle(
                                                          fontWeight:
                                                              FontWeight.bold,
                                                        ),
                                                      ),
                                                      const SizedBox(height: 6),
                                                      payments.isEmpty
                                                          ? const Text(
                                                              "Henüz ödeme yok",
                                                            )
                                                          : ListView.builder(
                                                              shrinkWrap: true,
                                                              physics:
                                                                  const NeverScrollableScrollPhysics(),
                                                              itemCount:
                                                                  payments
                                                                      .length,
                                                              itemBuilder: (context, i) {
                                                                final p =
                                                                    payments[i];
                                                                return ListTile(
                                                                  dense: true,
                                                                  contentPadding:
                                                                      EdgeInsets
                                                                          .zero,
                                                                  title: Text(
                                                                    "${p['amount']} TL",
                                                                  ),
                                                                  subtitle: Text(
                                                                    p['note'] ??
                                                                        '',
                                                                  ),
                                                                );
                                                              },
                                                            ),
                                                    ],
                                                  ),
                                                ),
                                              );
                                            },
                                          ),
                                          actions: [
                                            TextButton(
                                              onPressed: () =>
                                                  Navigator.pop(context),
                                              child: const Text("Kapat"),
                                            ),
                                            ElevatedButton(
                                              onPressed: () async {
                                                if (selectedStatus != null &&
                                                    selectedStatus !=
                                                        appt['status']) {
                                                  final url = Uri.parse(
                                                    "$apiBaseUrl/api/appointments/${appt['id']}",
                                                  );
                                                  final response = await http.put(
                                                    url,
                                                    headers: {
                                                      "Accept":
                                                          "application/json",
                                                      "Authorization":
                                                          "Bearer $globalToken",
                                                      "Content-Type":
                                                          "application/json",
                                                    },
                                                    body: jsonEncode({
                                                      "status": selectedStatus,
                                                    }),
                                                  );

                                                  if (response.statusCode ==
                                                      200) {
                                                    Navigator.pop(context);
                                                    fetchDashboardData();
                                                    ScaffoldMessenger.of(
                                                      context,
                                                    ).showSnackBar(
                                                      const SnackBar(
                                                        content: Text(
                                                          "Randevu durumu güncellendi.",
                                                        ),
                                                      ),
                                                    );
                                                  } else {
                                                    ScaffoldMessenger.of(
                                                      context,
                                                    ).showSnackBar(
                                                      const SnackBar(
                                                        content: Text(
                                                          "Randevu durumu güncellenemedi.",
                                                        ),
                                                      ),
                                                    );
                                                  }
                                                } else {
                                                  Navigator.pop(context);
                                                }
                                              },
                                              child: const Text("Kaydet"),
                                            ),
                                          ],
                                        );
                                      },
                                    );
                                  }
                                : null,
                            child: Container(
                              width: 180,
                              height: 40,
                              padding: const EdgeInsets.symmetric(
                                horizontal: 4,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                border: Border.all(color: Colors.grey.shade300),
                                color: appt != null
                                    ? (appt['status'] == 'pending'
                                          ? Colors.yellow.shade200
                                          : appt['status'] == 'completed'
                                          ? Colors.green.shade200
                                          : appt['status'] == 'canceled'
                                          ? Colors.red.shade200
                                          : Colors.blue.shade200)
                                    : Colors.white,
                              ),
                              child: appt != null
                                  ? Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        Text(
                                          "${appt['customer']['first_name']} ${appt['customer']['last_name']}",
                                          style: const TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                        Text(
                                          "${appt['status'] ?? 'Bilinmiyor'}"
                                          "  Ücret: "
                                          "${appt['Cost'] ?? '-'} TL",
                                          style: const TextStyle(
                                            fontSize: 11,
                                            color: Colors.black54,
                                          ),
                                        ),
                                      ],
                                    )
                                  : null,
                            ),
                          );
                        }).toList(),
                      ],
                    );
                  }).toList(),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
