/*
 * Serializer.cs
 * This is the Serializer class for the PHPSerializationLibrary
 *  
 * Copyright 2004 Conversive, Inc.
 * 
 */

using System;
using System.Text;
using System.Collections;

namespace Conversive.PHPSerializationLibrary
{
	/// <summary>
	/// Serializer Class.
	/// </summary>
	public class Serializer
	{
		//types:
		// s = string
		// i = int
		// d = double
		// a = array (hashtable)

		private ArrayList hashes; //for serialize (to infinte prevent loops)
		private int pos; //for unserialize

		public bool XMLSafe = true; //This member tells the serializer wether or not to strip carriage returns from strings when serializing and adding them back in when deserializing
									 //http://www.w3.org/TR/REC-xml/#sec-line-ends

		public Serializer()
		{
			//Do something here?
		}

		public string Serialize(object obj)
		{
			this.hashes = new ArrayList();
			return this.serialize(obj).ToString();
		}//Serialize(object obj)

		private StringBuilder serialize(object obj)
		{
			StringBuilder sb = new StringBuilder();
			if(obj == null)
			{
				return sb.Append("N;");
			}
			else if(obj is string)
			{
				string str = (string)obj;
				if(this.XMLSafe)
				{
					str = str.Replace("\r\n","\n");//replace \r\n with \n
					str = str.Replace("\r", "\n");//replace \r not followed by \n with a single \n  Should we do this?
				}
				return sb.Append("s:" + str.Length + ":\"" + str + "\";");
			}
			else if(obj is bool)
			{
				return sb.Append("b:" + (((bool)obj) ? "1" : "0") + ";");
			}
			else if(obj is int)
			{
				int i = (int)obj;
				return sb.Append("i:" + i + ";");
			}			
			else if(obj is double)
			{
				double d = (double)obj;
				return sb.Append("d:" + d + ";");
			}
			else if(obj is ArrayList)
			{
				if(this.hashes.Contains(obj.GetHashCode()))
					return sb.Append("N;");
				else
					this.hashes.Add(obj.GetHashCode());

				ArrayList a = (ArrayList)obj;
				sb.Append("a:" + a.Count + ":{");
				for(int i = 0; i < a.Count; i++)
				{
					sb.Append(this.serialize(i));
					sb.Append(this.serialize(a[i]));
				}
				sb.Append("}");
				return sb;
			}
			else if(obj is Hashtable)
			{
				if(this.hashes.Contains(obj.GetHashCode()))
					return sb.Append("N;");
				else
					this.hashes.Add(obj.GetHashCode());

				Hashtable a = (Hashtable)obj;
				sb.Append("a:" + a.Count + ":{");
				foreach(DictionaryEntry entry in a)
				{
					sb.Append(this.serialize(entry.Key));
					sb.Append(this.serialize(entry.Value));
				}
				sb.Append("}");
				return sb;
			}
			else
			{
				return sb;
			}
		}//Serialize(object obj)

		public object Deserialize(string str)
		{
			this.pos = 0;
			return deserialize(str);
		}//Deserialize(string str)

		private object deserialize(string str)
		{
			if(str == null || str.Length <= this.pos)
				return new Object();

			int start, end, length;
			string stLen;
			switch(str[this.pos])
			{
				case 'N':
					this.pos += 2;
					return null;
				case 'b':
					char chBool;
					chBool = str[pos + 2];
					this.pos += 4;
					return chBool == '1';
				case 'i':
					string stInt;
					start = str.IndexOf(":", this.pos) + 1;
					end = str.IndexOf(";", start);
					stInt = str.Substring(start, end - start);
					this.pos += 3 + stInt.Length;
					return Int32.Parse(stInt);
				case 'd':
					string stDouble;
					start = str.IndexOf(":", this.pos) + 1;
					end = str.IndexOf(";", start);
					stDouble = str.Substring(start, end - start);
					this.pos += 3 + stDouble.Length;
					return Double.Parse(stDouble);					
				case 's':
					//TODO: add bounds checking code
					start = str.IndexOf(":", this.pos) + 1;
					end = str.IndexOf(":", start);
					stLen = str.Substring(start, end - start);
					length = Int32.Parse(stLen);
					this.pos += 6 + stLen.Length + length;
					string stRet = str.Substring(end + 2, length);
					if(this.XMLSafe)
					{
						stRet = stRet.Replace("\n", "\r\n");
					}
					return stRet;
				case 'a':
					//if keys are ints 0 through N, returns an ArrayList, else returns Hashtable
					start = str.IndexOf(":", this.pos) + 1;
					end = str.IndexOf(":", start);
					stLen = str.Substring(start, end - start);
					length = Int32.Parse(stLen);
					Hashtable htRet = new Hashtable(length);
					ArrayList alRet = new ArrayList(length);
					this.pos += 4 + stLen.Length; //a:Len:{
					for(int i = 0; i < length; i++)
					{
						//read key
						object key = deserialize(str);
						//read value
						object val = deserialize(str);

						if(alRet != null)
						{
							if(key is int && (int)key == alRet.Count)
								alRet.Add(val);
							else
								alRet = null;
						}
						htRet[key] = val;
					}
					this.pos++; //skip the }
					if(this.pos < str.Length && str[this.pos] == ';')//skipping our old extra array semi-colon bug (er... php's weirdness)
						this.pos++;
					if(alRet != null)
						return alRet;
					else
						return htRet;
				default:
					return "";
			}//switch
		}//unserialzie(object)

		public void TestSerialize()
		{
			string errors = "";
			if(this.Serialize(false) != "b:0;")
				errors += "serialize bool failed, ";
			if(this.Serialize(1) != "i:1;")
				errors += "serialize int failed, ";
			if(this.Serialize(3.14159) != "d:3.14159;")
				errors += "serialize double failed, ";
			if(this.Serialize("hello world") != "s:11:\"hello world\";")
				errors += "serialize string failed, ";
			Hashtable ht = new Hashtable();
			ht.Add(0, "zero");
			string target = "a:1:{i:0;s:4:\"zero\";};";
			string result = this.Serialize(ht);
			if(result != target)
				errors += "serialize array failed, ";

			if(this.Serialize("hello world") != "s:11:\"hello world\";")
				errors += "serialize string failed, ";
			
			if(errors.Length != 0)
				throw new Exception(errors);
		}

		public void TestUnserialize()
		{
			if(((string)this.Deserialize("s:11:\"hello world\";")) != "hello world")
				throw new Exception("unserialize failed");
		}

		public void TestComplex()
		{
			string errors = "";

			Hashtable ht = new Hashtable();
			ht.Add(0, false);
			ht.Add(1, 1.5);
			ht.Add(2, "two");
			ht.Add("self", ht);
			
			string result = this.Serialize(ht);
			Hashtable unht = (Hashtable)this.Deserialize(result);

			if((bool)unht[0] != false
				|| (double)unht[1] != 1.5
				|| (string)unht[2] != "two"
				|| unht["self"] != null)
				errors += "serialize complex failed, ";
			
			if(errors.Length != 0)
				throw new Exception(errors);
		}
	
	}//class Serializer
}
