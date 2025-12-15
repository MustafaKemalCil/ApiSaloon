import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import '../global.dart';

class CustomerAddPage extends StatefulWidget {
  const CustomerAddPage({super.key});

  @override
  State<CustomerAddPage> createState() => _CustomerAddPageState();
}

class _CustomerAddPageState extends State<CustomerAddPage> {
  final _formKey = GlobalKey<FormState>();

  final TextEditingController firstNameCtrl = TextEditingController();
  final TextEditingController lastNameCtrl = TextEditingController();
  final TextEditingController phoneCtrl = TextEditingController();
  final TextEditingController emailCtrl = TextEditingController();
  final TextEditingController noteCtrl = TextEditingController();

  String gender = 'female';
  bool loading = false;

  Future<void> submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => loading = true);

    final url = Uri.parse('$apiBaseUrl/api/customers');

    try {
      final response = await http.post(
        url,
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "Authorization": "Bearer $globalToken",
        },
        body: jsonEncode({
          "first_name": firstNameCtrl.text,
          "last_name": lastNameCtrl.text,
          "gender": gender,
          "phone": phoneCtrl.text,
          "email": emailCtrl.text,
          "note": noteCtrl.text,
        }),
      );

      setState(() => loading = false);

      if (response.statusCode == 200 || response.statusCode == 201) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text("✅ Müşteri başarıyla eklendi")),
        );
        Navigator.pop(context, true); // listeye geri dön
      } else {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text("❌ Hata: ${response.body}")));
      }
    } catch (e) {
      setState(() => loading = false);
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text("❌ Bağlantı hatası: $e")));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Müşteri Ekle")),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              TextFormField(
                controller: firstNameCtrl,
                decoration: const InputDecoration(labelText: "Ad"),
                validator: (v) => v!.isEmpty ? "Ad gerekli" : null,
              ),
              TextFormField(
                controller: lastNameCtrl,
                decoration: const InputDecoration(labelText: "Soyad"),
                validator: (v) => v!.isEmpty ? "Soyad gerekli" : null,
              ),
              DropdownButtonFormField(
                value: gender,
                decoration: const InputDecoration(labelText: "Cinsiyet"),
                items: const [
                  DropdownMenuItem(value: 'female', child: Text("Kadın")),
                  DropdownMenuItem(value: 'male', child: Text("Erkek")),
                ],
                onChanged: (v) {
                  setState(() => gender = v.toString());
                },
              ),
              TextFormField(
                controller: phoneCtrl,
                decoration: const InputDecoration(labelText: "Telefon"),
              ),
              TextFormField(
                controller: emailCtrl,
                decoration: const InputDecoration(labelText: "E-posta"),
                keyboardType: TextInputType.emailAddress,
              ),
              TextFormField(
                controller: noteCtrl,
                decoration: const InputDecoration(labelText: "Not"),
                maxLines: 3,
              ),
              const SizedBox(height: 20),
              SizedBox(
                height: 50,
                child: ElevatedButton(
                  onPressed: loading ? null : submit,
                  child: loading
                      ? const CircularProgressIndicator(color: Colors.white)
                      : const Text("Kaydet"),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
