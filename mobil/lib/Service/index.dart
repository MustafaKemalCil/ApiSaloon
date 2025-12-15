import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../global.dart';

class ServicePage extends StatefulWidget {
  const ServicePage({super.key});

  @override
  State<ServicePage> createState() => _ServicePageState();
}

class _ServicePageState extends State<ServicePage> {
  List services = [];
  bool loading = true;

  final TextEditingController nameController = TextEditingController();
  final TextEditingController descController = TextEditingController();
  final TextEditingController costController = TextEditingController();

  int? editingId; // null = ekleme, dolu = düzenleme

  @override
  void initState() {
    super.initState();
    fetchServices();
  }

  /// GET - Servisleri getir
  Future<void> fetchServices() async {
    setState(() => loading = true);
    try {
      final res = await http.get(
        Uri.parse("$apiBaseUrl/api/service"),
        headers: {
          "Authorization": "Bearer $globalToken",
          "Accept": "application/json",
        },
      );

      if (res.statusCode == 200) {
        services = jsonDecode(res.body);
      }
    } catch (_) {}

    setState(() => loading = false);
  }

  /// Popup aç (Ekle / Düzenle)
  void openServiceDialog({Map? service}) {
    if (service != null) {
      editingId = service['id'];
      nameController.text = service['name'];
      descController.text = service['description'] ?? "";
      costController.text = service['cost'].toString();
    } else {
      editingId = null;
      nameController.clear();
      descController.clear();
      costController.clear();
    }

    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: Text(editingId == null ? "Hizmet Ekle" : "Hizmet Düzenle"),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: nameController,
              decoration: const InputDecoration(labelText: "Hizmet Adı"),
            ),
            TextField(
              controller: descController,
              decoration: const InputDecoration(labelText: "Açıklama"),
            ),
            TextField(
              controller: costController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(labelText: "Ücret"),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("İptal"),
          ),
          ElevatedButton(onPressed: saveService, child: const Text("Kaydet")),
        ],
      ),
    );
  }

  /// POST / PUT - Kaydet
  Future<void> saveService() async {
    final payload = {
      "name": nameController.text,
      "description": descController.text,
      "cost": costController.text,
    };

    final url = editingId == null
        ? "$apiBaseUrl/api/service"
        : "$apiBaseUrl/api/service/$editingId";

    try {
      final res = await http.post(
        Uri.parse(url),
        headers: {
          "Authorization": "Bearer $globalToken",
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: jsonEncode(payload),
      );

      if (res.statusCode == 200 || res.statusCode == 201) {
        Navigator.pop(context);
        fetchServices();
      }
    } catch (_) {}
  }

  /// DELETE - Sil
  Future<void> deleteService(int id) async {
    bool? confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text("Silme Onayı"),
        content: const Text("Bu hizmeti silmek istediğinize emin misiniz?"),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text("İptal"),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
            onPressed: () => Navigator.pop(context, true),
            child: const Text("Sil"),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    try {
      await http.delete(
        Uri.parse("$apiBaseUrl/api/service/$id"),
        headers: {
          "Authorization": "Bearer $globalToken",
          "Accept": "application/json",
        },
      );
      fetchServices();
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Hizmetler"),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () => openServiceDialog(),
          ),
        ],
      ),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: services.length,
              itemBuilder: (_, i) {
                final s = services[i];
                return Card(
                  margin: const EdgeInsets.all(8),
                  child: ListTile(
                    title: Text(s['name']),
                    subtitle: Text("${s['description'] ?? ''}\n${s['cost']} ₺"),
                    isThreeLine: true,
                    trailing: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        IconButton(
                          icon: const Icon(Icons.edit, color: Colors.orange),
                          onPressed: () => openServiceDialog(service: s),
                        ),
                        IconButton(
                          icon: const Icon(Icons.delete, color: Colors.red),
                          onPressed: () => deleteService(s['id']),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
    );
  }
}
