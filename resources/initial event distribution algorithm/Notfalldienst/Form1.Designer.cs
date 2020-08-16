namespace Notfalldienst
{
    partial class Form1
    {
        /// <summary>
        /// Required designer variable.
        /// </summary>
        private System.ComponentModel.IContainer components = null;

        /// <summary>
        /// Clean up any resources being used.
        /// </summary>
        /// <param name="disposing">true if managed resources should be disposed; otherwise, false.</param>
        protected override void Dispose(bool disposing)
        {
            if (disposing && (components != null))
            {
                components.Dispose();
            }
            base.Dispose(disposing);
        }

        #region Windows Form Designer generated code

        /// <summary>
        /// Required method for Designer support - do not modify
        /// the contents of this method with the code editor.
        /// </summary>
        private void InitializeComponent()
        {
            this.button1 = new System.Windows.Forms.Button();
            this.fileopen = new System.Windows.Forms.Button();
            this.tierartztepath = new System.Windows.Forms.TextBox();
            this.Start = new System.Windows.Forms.DateTimePicker();
            this.label2 = new System.Windows.Forms.Label();
            this.Ende = new System.Windows.Forms.DateTimePicker();
            this.label3 = new System.Windows.Forms.Label();
            this.openFileDialog1 = new System.Windows.Forms.OpenFileDialog();
            this.feiertage = new System.Windows.Forms.RichTextBox();
            this.label5 = new System.Windows.Forms.Label();
            this.richTextBox2 = new System.Windows.Forms.RichTextBox();
            this.textBox2 = new System.Windows.Forms.TextBox();
            this.label12 = new System.Windows.Forms.Label();
            this.scorejsonpath = new System.Windows.Forms.TextBox();
            this.button2 = new System.Windows.Forms.Button();
            this.openFileDialog2 = new System.Windows.Forms.OpenFileDialog();
            this.savePath = new System.Windows.Forms.TextBox();
            this.button3 = new System.Windows.Forms.Button();
            this.folderBrowserDialog1 = new System.Windows.Forms.FolderBrowserDialog();
            this.historyjsonpath = new System.Windows.Forms.TextBox();
            this.button4 = new System.Windows.Forms.Button();
            this.minabstand = new System.Windows.Forms.TextBox();
            this.triesproabstand = new System.Windows.Forms.TextBox();
            this.label1 = new System.Windows.Forms.Label();
            this.label4 = new System.Windows.Forms.Label();
            this.SuspendLayout();
            // 
            // button1
            // 
            this.button1.Enabled = false;
            this.button1.Location = new System.Drawing.Point(752, 67);
            this.button1.Name = "button1";
            this.button1.Size = new System.Drawing.Size(232, 52);
            this.button1.TabIndex = 0;
            this.button1.Text = "Start";
            this.button1.UseVisualStyleBackColor = true;
            this.button1.Click += new System.EventHandler(this.button1_Click);
            // 
            // fileopen
            // 
            this.fileopen.Location = new System.Drawing.Point(519, 10);
            this.fileopen.Name = "fileopen";
            this.fileopen.Size = new System.Drawing.Size(227, 23);
            this.fileopen.TabIndex = 10;
            this.fileopen.Text = "Tierärzte auswählen";
            this.fileopen.UseVisualStyleBackColor = true;
            this.fileopen.Click += new System.EventHandler(this.fileopen_Click);
            // 
            // tierartztepath
            // 
            this.tierartztepath.Enabled = false;
            this.tierartztepath.Location = new System.Drawing.Point(12, 12);
            this.tierartztepath.Name = "tierartztepath";
            this.tierartztepath.Size = new System.Drawing.Size(495, 20);
            this.tierartztepath.TabIndex = 33;
            // 
            // Start
            // 
            this.Start.Location = new System.Drawing.Point(131, 176);
            this.Start.Name = "Start";
            this.Start.Size = new System.Drawing.Size(200, 20);
            this.Start.TabIndex = 34;
            this.Start.Value = new System.DateTime(2016, 1, 7, 0, 0, 0, 0);
            // 
            // label2
            // 
            this.label2.Font = new System.Drawing.Font("Verdana", 10F);
            this.label2.Location = new System.Drawing.Point(9, 173);
            this.label2.Name = "label2";
            this.label2.Size = new System.Drawing.Size(86, 23);
            this.label2.TabIndex = 31;
            this.label2.Text = "Start";
            // 
            // Ende
            // 
            this.Ende.Location = new System.Drawing.Point(131, 202);
            this.Ende.Name = "Ende";
            this.Ende.Size = new System.Drawing.Size(200, 20);
            this.Ende.TabIndex = 36;
            this.Ende.Value = new System.DateTime(2017, 1, 7, 0, 0, 0, 0);
            // 
            // label3
            // 
            this.label3.Font = new System.Drawing.Font("Verdana", 10F);
            this.label3.Location = new System.Drawing.Point(9, 196);
            this.label3.Name = "label3";
            this.label3.Size = new System.Drawing.Size(86, 23);
            this.label3.TabIndex = 35;
            this.label3.Text = "Ende";
            // 
            // openFileDialog1
            // 
            this.openFileDialog1.FileName = "openFileDialog1";
            this.openFileDialog1.Filter = "csv files (*.csv)|*.csv|All files (*.*)|*.*";
            this.openFileDialog1.RestoreDirectory = true;
            // 
            // feiertage
            // 
            this.feiertage.Location = new System.Drawing.Point(9, 251);
            this.feiertage.Name = "feiertage";
            this.feiertage.Size = new System.Drawing.Size(319, 261);
            this.feiertage.TabIndex = 37;
            this.feiertage.Text = "1.1.2016\n2.1.2016\n3.1.2016\n24.4.2016\n25.4.2016\n26.4.2016\n27.4.2016\n28.4.2016\n1.5." +
    "2016\n4.5.2016\n5.5.2016\n14.5.2016\n15.5.2016\n16.5.2016\n1.8.2016\n24.12.2016\n25.12.2" +
    "016\n26.12.2016\n31.12.2016\n1.1.2017";
            // 
            // label5
            // 
            this.label5.Font = new System.Drawing.Font("Verdana", 10F);
            this.label5.Location = new System.Drawing.Point(9, 225);
            this.label5.Name = "label5";
            this.label5.Size = new System.Drawing.Size(284, 23);
            this.label5.TabIndex = 38;
            this.label5.Text = "Feiertage (ein Eintrag pro Zeile):";
            // 
            // richTextBox2
            // 
            this.richTextBox2.Location = new System.Drawing.Point(334, 149);
            this.richTextBox2.Name = "richTextBox2";
            this.richTextBox2.Size = new System.Drawing.Size(629, 363);
            this.richTextBox2.TabIndex = 39;
            this.richTextBox2.Text = "";
            // 
            // textBox2
            // 
            this.textBox2.Location = new System.Drawing.Point(294, 149);
            this.textBox2.Name = "textBox2";
            this.textBox2.Size = new System.Drawing.Size(37, 20);
            this.textBox2.TabIndex = 44;
            this.textBox2.KeyUp += new System.Windows.Forms.KeyEventHandler(this.textBox2_KeyUp);
            // 
            // label12
            // 
            this.label12.Font = new System.Drawing.Font("Verdana", 10F);
            this.label12.Location = new System.Drawing.Point(9, 150);
            this.label12.Name = "label12";
            this.label12.Size = new System.Drawing.Size(279, 23);
            this.label12.TabIndex = 43;
            this.label12.Text = "id Wochentelefon in der ersten Woche";
            // 
            // scorejsonpath
            // 
            this.scorejsonpath.Enabled = false;
            this.scorejsonpath.Location = new System.Drawing.Point(12, 38);
            this.scorejsonpath.Name = "scorejsonpath";
            this.scorejsonpath.Size = new System.Drawing.Size(495, 20);
            this.scorejsonpath.TabIndex = 46;
            // 
            // button2
            // 
            this.button2.Location = new System.Drawing.Point(519, 36);
            this.button2.Name = "button2";
            this.button2.Size = new System.Drawing.Size(227, 23);
            this.button2.TabIndex = 45;
            this.button2.Text = "score.json Datei auswählen";
            this.button2.UseVisualStyleBackColor = true;
            this.button2.Click += new System.EventHandler(this.button2_Click);
            // 
            // openFileDialog2
            // 
            this.openFileDialog2.FileName = "openFileDialog2";
            this.openFileDialog2.Filter = "json files (*.json)|*.json|All files (*.*)|*.*";
            // 
            // savePath
            // 
            this.savePath.Enabled = false;
            this.savePath.Location = new System.Drawing.Point(12, 93);
            this.savePath.Name = "savePath";
            this.savePath.Size = new System.Drawing.Size(495, 20);
            this.savePath.TabIndex = 47;
            // 
            // button3
            // 
            this.button3.Location = new System.Drawing.Point(519, 94);
            this.button3.Name = "button3";
            this.button3.Size = new System.Drawing.Size(227, 23);
            this.button3.TabIndex = 48;
            this.button3.Text = "Speicherordner festlegen";
            this.button3.UseVisualStyleBackColor = true;
            this.button3.Click += new System.EventHandler(this.button3_Click);
            // 
            // historyjsonpath
            // 
            this.historyjsonpath.Enabled = false;
            this.historyjsonpath.Location = new System.Drawing.Point(12, 67);
            this.historyjsonpath.Name = "historyjsonpath";
            this.historyjsonpath.Size = new System.Drawing.Size(495, 20);
            this.historyjsonpath.TabIndex = 50;
            // 
            // button4
            // 
            this.button4.Location = new System.Drawing.Point(519, 65);
            this.button4.Name = "button4";
            this.button4.Size = new System.Drawing.Size(227, 23);
            this.button4.TabIndex = 49;
            this.button4.Text = "history.json Datei auswählen";
            this.button4.UseVisualStyleBackColor = true;
            this.button4.Click += new System.EventHandler(this.button4_Click);
            // 
            // minabstand
            // 
            this.minabstand.Location = new System.Drawing.Point(947, 14);
            this.minabstand.Name = "minabstand";
            this.minabstand.Size = new System.Drawing.Size(37, 20);
            this.minabstand.TabIndex = 51;
            this.minabstand.Text = "8";
            // 
            // triesproabstand
            // 
            this.triesproabstand.Location = new System.Drawing.Point(947, 38);
            this.triesproabstand.Name = "triesproabstand";
            this.triesproabstand.Size = new System.Drawing.Size(37, 20);
            this.triesproabstand.TabIndex = 52;
            this.triesproabstand.Text = "2000";
            // 
            // label1
            // 
            this.label1.AutoSize = true;
            this.label1.Location = new System.Drawing.Point(753, 15);
            this.label1.Name = "label1";
            this.label1.Size = new System.Drawing.Size(185, 13);
            this.label1.TabIndex = 53;
            this.label1.Text = "Minimaler Abstand zwischen Diensten";
            // 
            // label4
            // 
            this.label4.AutoSize = true;
            this.label4.Location = new System.Drawing.Point(753, 41);
            this.label4.Name = "label4";
            this.label4.Size = new System.Drawing.Size(112, 13);
            this.label4.TabIndex = 54;
            this.label4.Text = "Versuche pro Abstand";
            // 
            // Form1
            // 
            this.AutoScaleDimensions = new System.Drawing.SizeF(6F, 13F);
            this.AutoScaleMode = System.Windows.Forms.AutoScaleMode.Font;
            this.ClientSize = new System.Drawing.Size(1007, 518);
            this.Controls.Add(this.label4);
            this.Controls.Add(this.label1);
            this.Controls.Add(this.triesproabstand);
            this.Controls.Add(this.minabstand);
            this.Controls.Add(this.historyjsonpath);
            this.Controls.Add(this.button4);
            this.Controls.Add(this.button3);
            this.Controls.Add(this.savePath);
            this.Controls.Add(this.scorejsonpath);
            this.Controls.Add(this.button2);
            this.Controls.Add(this.textBox2);
            this.Controls.Add(this.label12);
            this.Controls.Add(this.richTextBox2);
            this.Controls.Add(this.label5);
            this.Controls.Add(this.feiertage);
            this.Controls.Add(this.Ende);
            this.Controls.Add(this.label3);
            this.Controls.Add(this.Start);
            this.Controls.Add(this.tierartztepath);
            this.Controls.Add(this.label2);
            this.Controls.Add(this.fileopen);
            this.Controls.Add(this.button1);
            this.Name = "Form1";
            this.Text = "+";
            this.ResumeLayout(false);
            this.PerformLayout();

        }

        #endregion

        private System.Windows.Forms.Button button1;
        private System.Windows.Forms.Button fileopen;
        private System.Windows.Forms.TextBox tierartztepath;
        private System.Windows.Forms.DateTimePicker Start;
        private System.Windows.Forms.Label label2;
        private System.Windows.Forms.DateTimePicker Ende;
        private System.Windows.Forms.Label label3;
        private System.Windows.Forms.OpenFileDialog openFileDialog1;
        private System.Windows.Forms.RichTextBox feiertage;
        private System.Windows.Forms.Label label5;
        private System.Windows.Forms.RichTextBox richTextBox2;
        private System.Windows.Forms.TextBox textBox2;
        private System.Windows.Forms.Label label12;
        private System.Windows.Forms.TextBox scorejsonpath;
        private System.Windows.Forms.Button button2;
        private System.Windows.Forms.OpenFileDialog openFileDialog2;
        private System.Windows.Forms.TextBox savePath;
        private System.Windows.Forms.Button button3;
        private System.Windows.Forms.FolderBrowserDialog folderBrowserDialog1;
        private System.Windows.Forms.TextBox historyjsonpath;
        private System.Windows.Forms.Button button4;
        private System.Windows.Forms.TextBox minabstand;
        private System.Windows.Forms.TextBox triesproabstand;
        private System.Windows.Forms.Label label1;
        private System.Windows.Forms.Label label4;
    }
}

