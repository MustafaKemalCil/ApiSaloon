import 'dart:convert';
import 'package:flutter/material.dart';
import '../global.dart'; // burada globalToken ve apiBaseUrl tanımlı
import 'package:http/http.dart' as http;

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  bool loading = true;
  TextEditingController firstNameController = TextEditingController();
  TextEditingController lastNameController = TextEditingController();
  TextEditingController emailController = TextEditingController();
  TextEditingController phoneController = TextEditingController();
  final _formKey = GlobalKey<FormState>();
  @override
  void initState() {
    super.initState();
    fetchProfile();
  }

  Future<void> fetchProfile() async {
    if (globalToken == null) {
      print("Token null, fetch yapılamıyor");
      return;
    }

    final url = '$apiBaseUrl/api/profile';
    print("Fetching profile from $url"); // URL'i logla

    setState(() => loading = true);

    try {
      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Authorization': 'Bearer $globalToken',
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      );

      print("Status code: ${response.statusCode}"); // Status code'u logla
      print("Response body: ${response.body}"); // Response body'i logla

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        print("Parsed data: $data"); // Parse edilmiş JSON'i logla

        setState(() {
          firstNameController.text = data['first_name'] ?? '';
          lastNameController.text = data['last_name'] ?? '';
          emailController.text = data['email'] ?? '';
          phoneController.text = data['phone'] ?? '';
          loading = false;
        });
      } else {
        setState(() => loading = false);
        print("Profil bilgileri yüklenemedi: ${response.statusCode}");
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Profil bilgileri yüklenemedi. Status: ${response.statusCode}',
            ),
          ),
        );
      }
    } catch (e) {
      setState(() => loading = false);
      print("Hata oluştu: $e");
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Hata oluştu: $e')));
    }
  }

  Future<void> updateProfile() async {
    if (globalToken == null) return;

    final response = await http.post(
      Uri.parse('$apiBaseUrl/api/profile/update'),
      headers: {
        'Authorization': 'Bearer $globalToken',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: json.encode({
        'first_name': firstNameController.text,
        'last_name': lastNameController.text,
        'email': emailController.text,
        'phone': phoneController.text,
      }),
    );

    final data = json.decode(response.body);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(data['message'] ?? 'Profil güncelleme başarısız')),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (loading) {
      return const Center(child: CircularProgressIndicator());
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Form(
        key: _formKey, // Form validasyonu için
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              "Profil Bilgileri",
              style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),

            // Buraya TextFormField'lar geliyor
            TextFormField(
              controller: firstNameController, // <-- buraya
              decoration: const InputDecoration(labelText: "Ad"),
              validator: (value) => value!.isEmpty ? 'Bu alan zorunlu' : null,
            ),
            const SizedBox(height: 10),

            TextFormField(
              controller: lastNameController,
              decoration: const InputDecoration(labelText: "Soyad"),
              validator: (value) => value!.isEmpty ? 'Bu alan zorunlu' : null,
            ),
            const SizedBox(height: 10),

            TextFormField(
              controller: emailController,
              decoration: const InputDecoration(labelText: "Email"),
              validator: (value) => value!.isEmpty ? 'Bu alan zorunlu' : null,
            ),
            const SizedBox(height: 10),

            TextFormField(
              controller: phoneController,
              decoration: const InputDecoration(labelText: "Telefon"),
            ),
            const SizedBox(height: 20),

            ElevatedButton(
              onPressed: updateProfile,
              child: const Text("Profil Güncelle"),
            ),
          ],
        ),
      ),
    );
  }
}
